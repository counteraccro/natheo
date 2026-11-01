<?php

declare(strict_types=1);
/**
 * Test du voter ContentVoter
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Tests\Security\Voter;

use App\Entity\Admin\System\User;
use App\Enum\Admin\System\Options\OptionSystem;
use App\Security\Voter\ContentVoter;
use App\Service\Admin\System\OptionSystemService;
use App\Tests\AppWebTestCase;
use Symfony\Component\Security\Core\Authentication\Token\NullToken;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class ContentVoterTest extends AppWebTestCase
{
    private ContentVoter $voter;

    private OptionSystemService $optionSystemService;

    public function setUp(): void
    {
        parent::setUp();
        $this->optionSystemService = $this->container->get(OptionSystemService::class);
        $this->voter = new ContentVoter(
            $this->container->get(AccessDecisionManagerInterface::class),
            $this->optionSystemService,
        );
        $this->setAllowDelete(true);
    }

    /**
     * Un contributeur gère ses propres contenus (Page, Faq, Menu, Tag)
     * @return void
     */
    public function testOwnerIsGranted(): void
    {
        $user = $this->createUserContributeur();
        $subjects = [
            $this->createPage($user),
            $this->createFaq($user),
            $this->createMenu($user),
            $this->createTag($user),
        ];

        foreach ($subjects as $subject) {
            foreach ([ContentVoter::EDIT, ContentVoter::DISABLE, ContentVoter::DELETE] as $attribute) {
                $this->assertSame(
                    VoterInterface::ACCESS_GRANTED,
                    $this->vote($user, $subject, $attribute),
                    $subject::class . ' / ' . $attribute,
                );
            }
        }
    }

    /**
     * Un contributeur ne gère pas les contenus d'un autre
     * @return void
     */
    public function testOtherContributorIsDenied(): void
    {
        $owner = $this->createUserContributeur();
        $other = $this->createUserContributeur();
        $page = $this->createPage($owner);

        foreach ([ContentVoter::EDIT, ContentVoter::DISABLE, ContentVoter::DELETE] as $attribute) {
            $this->assertSame(VoterInterface::ACCESS_DENIED, $this->vote($other, $page, $attribute));
        }
    }

    /**
     * Un admin gère les contenus des autres
     * @return void
     */
    public function testAdminIsGranted(): void
    {
        $admin = $this->createUserAdmin();
        $faq = $this->createFaq($this->createUserContributeur());

        foreach ([ContentVoter::EDIT, ContentVoter::DISABLE, ContentVoter::DELETE] as $attribute) {
            $this->assertSame(VoterInterface::ACCESS_GRANTED, $this->vote($admin, $faq, $attribute));
        }
    }

    /**
     * Un contenu sans propriétaire n'est géré que par un admin
     * @return void
     */
    public function testWithoutOwner(): void
    {
        $tag = $this->createTag();
        $tag->setUser(null);
        $this->persistAndFlush($tag);

        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $this->vote($this->createUserContributeur(), $tag, ContentVoter::EDIT),
        );
        $this->assertSame(VoterInterface::ACCESS_GRANTED, $this->vote($this->createUserAdmin(), $tag, ContentVoter::EDIT));
    }

    /**
     * L'option de suppression désactivée bloque DELETE, y compris pour l'admin, sans bloquer EDIT
     * @return void
     */
    public function testDeleteOptionDisabled(): void
    {
        $this->setAllowDelete(false);
        $admin = $this->createUserAdmin();
        $menu = $this->createMenu($admin);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->vote($admin, $menu, ContentVoter::DELETE));
        $this->assertSame(VoterInterface::ACCESS_GRANTED, $this->vote($admin, $menu, ContentVoter::EDIT));
    }

    /**
     * Attribut ou sujet non géré : abstention ; utilisateur non connecté : refus
     * @return void
     */
    public function testUnsupportedAndAnonymous(): void
    {
        $user = $this->createUserContributeur();
        $page = $this->createPage($user);

        $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $this->vote($user, $page, 'OTHER_ATTRIBUTE'));
        $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $this->vote($user, $user, ContentVoter::EDIT));
        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->voter->vote(new NullToken(), $page, [ContentVoter::EDIT]));
    }

    /**
     * @param User $user
     * @param mixed $subject
     * @param string $attribute
     * @return int
     */
    private function vote(User $user, mixed $subject, string $attribute): int
    {
        $token = new UsernamePasswordToken($user, 'admin', $user->getRoles());
        return $this->voter->vote($token, $subject, [$attribute]);
    }

    /**
     * @param bool $allow
     * @return void
     */
    private function setAllowDelete(bool $allow): void
    {
        $this->optionSystemService->saveValueByKee(OptionSystem::OS_ALLOW_DELETE_DATA->value, $allow ? '1' : '0');
    }
}
