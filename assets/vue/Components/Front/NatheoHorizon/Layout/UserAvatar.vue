<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Avatar de l'utilisateur connecté, initiale de son identifiant sans image
 */
import type { FrontUser } from '@/ts/Front/FrontProps.type';

defineProps<{ user: FrontUser }>();
</script>

<template>
  <img v-if="user.avatar" :src="user.avatar" alt="" class="size-8 rounded-nh object-cover" />
  <span
    v-else
    class="grid size-8 place-items-center rounded-nh bg-mod-half text-[0.85rem] font-bold text-on-mod"
    aria-hidden="true"
  >
    {{ user.login.slice(0, 1).toUpperCase() }}
  </span>
</template>
