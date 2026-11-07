# Politique de sécurité

## Signaler une faille

**N'ouvrez pas d'issue publique.** Une faille décrite publiquement peut être exploitée avant d'être corrigée.

Signalez-la en privé, au choix :
- via le formulaire GitHub [« Report a vulnerability »](https://github.com/counteraccro/natheo/security/advisories/new), visible uniquement par les mainteneurs ;
- en message privé sur Discord : `counteraccro`.

Indiquez si possible : la version du CMS, la route ou l'écran concerné, les étapes pour reproduire, et l'impact estimé.

## Ce qui se passe ensuite

1. Accusé de réception sous 7 jours.
2. Analyse et correctif, suivis dans une advisory privée.
3. Publication d'une version corrigée pour chaque version suivie concernée, puis de l'advisory (avec mention de l'auteur du signalement s'il le souhaite).

## Versions suivies

| Version | Correctifs de sécurité |
|---|---|
| 2.1 (en développement, branche `master`) | ✅ |
| 2.0.x (branche `release/2.0`) | ✅ |
| 1.x | ❌ |

## Failles corrigées

**Aucune faille connue dans la version actuelle (2.0.1).**

Les advisories publiées dans l'onglet Security concernent des failles déjà corrigées. Elles restent publiées pour informer les utilisateurs des versions antérieures, qui doivent mettre à jour.

| Advisory | Sévérité | Versions touchées | Corrigée dans |
|---|---|---|---|
| [GHSA-m6wx-pj44-98rm](https://github.com/counteraccro/natheo/security/advisories/GHSA-m6wx-pj44-98rm) : XSS, injection DQL et CSRF dans l'administration des contenus | Élevée | < 2.0.0 | 2.0.0 |
| [GHSA-6qw2-2pv6-qjx7](https://github.com/counteraccro/natheo/security/advisories/GHSA-6qw2-2pv6-qjx7) : absence de protection CSRF sur les actions du compte et des notifications | Moyenne | < 2.0.0 | 2.0.0 |

## Pour les mainteneurs : traiter une faille

1. Créer une **advisory en brouillon** (`Security > Advisories > New draft`), qui contient la description complète : scénario, requêtes, fichiers.
2. Corriger dans le fork privé temporaire de l'advisory, ou dans une branche dont l'intitulé ne décrit pas la faille.
3. Côté public, l'issue et la PR (label `Sécurité`) restent **neutres** : ce qui est renforcé, sans scénario d'exploitation. Le détail reste dans l'advisory.
4. Après la sortie de la version corrigée : publier l'advisory, l'ajouter au tableau « Failles corrigées » (et mettre à jour la version actuelle), et demander un CVE si besoin.
