import { afterEach } from 'vitest';
import { enableAutoUnmount } from '@vue/test-utils';

// Chaque test démonte ses composants : aucun écouteur ni minuteur ne fuit dans le suivant
enableAutoUnmount(afterEach);
