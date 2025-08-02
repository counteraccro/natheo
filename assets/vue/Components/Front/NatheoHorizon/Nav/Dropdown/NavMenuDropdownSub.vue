<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Sous menu dropdown
 */
export default {
  name: 'NavMenuDropdownSub',
  props: {
    data: Object,
    utilsFront: Object
  },
  emits: [],
  data() {
    return {}
  },
  mounted() {

  },

  methods: {}
}
</script>

<template>

  <a href="#" class="flex items-center justify-between px-4 py-2 !text-gray-500 hover:bg-theme-4-750 hover:!text-theme-1-100 rounded-md">
    {{ this.data.label }}
    <svg xmlns="http://www.w3.org/2000/svg" class="size-5 inline-block -rotate-90 ml-2" viewBox="0 0 20 20">
      <path fill-rule="evenodd"
            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
            clip-rule="evenodd"/>
    </svg>
  </a>

  <div
      class="group-hover/level-1:block group-focus-within/level-1:block group-hover/level-1:opacity-100 group-hover/level-1:translate-x-0 group-focus-within/level-1:opacity-100 group-focus-within/level-1:translate-x-0 absolute left-full top-0 z-20 min-w-max hidden translate-x-2 transition-all duration-200 ease-out bg-white border border-gray-200 rounded-lg shadow-lg opacity-0">
    <ul class="py-2 px-4">

      <li v-for="element in this.data.elements" class="relative group/level-2 focus-within:bg-theme-4-750 focus-within:rounded-md focus-within:text-shadow-theme-1-100">
        <a :href="this.utilsFront.getUrl(element)" v-if="!element.hasOwnProperty('elements')"
           class="block px-4 py-2 hover:bg-theme-4-750 !text-gray-500 hover:!text-theme-1-100 rounded-md">{{ element.label }}</a>

        <a href="#" class="flex items-center justify-between px-4 py-2 hover:bg-theme-4-750 !text-gray-500 hover:!text-theme-1-100 rounded-md"
           v-if="element.hasOwnProperty('elements')">
          {{ element.label }}
          <svg xmlns="http://www.w3.org/2000/svg" class="size-5 inline-block -rotate-90 ml-2" viewBox="0 0 20 20">
            <path fill-rule="evenodd"
                  d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                  clip-rule="evenodd"/>
          </svg>
        </a>
        <div v-if="element.hasOwnProperty('elements')" class="absolute left-full top-0 bg-white border border-gray-200 rounded-lg shadow-lg z-30 min-w-max hidden group-hover/level-2:block group-focus-within/level-2:block
                          opacity-0 translate-x-2 transition-all duration-200 ease-out group-hover/level-2:opacity-100 group-hover/level-2:translate-x-0 group-focus-within/level-2:opacity-100 group-focus-within/level-2:translate-x-0">
          <ul class="py-2 px-4">

            <li v-for="element3 in element.elements" class="relative group/level-3 focus-within:bg-theme-4-750 focus-within:rounded-md focus-within:text-shadow-theme-1-100">
              <a :href="this.utilsFront.getUrl(element3)" v-if="!element3.hasOwnProperty('elements')"
                 class="block px-4 py-2 hover:bg-theme-4-750 !text-gray-500 hover:!text-theme-1-100 rounded-md">{{ element3.label }}</a>
              <a href="#" class="flex items-center justify-between px-4 py-2 hover:bg-theme-4-750 !text-gray-500 hover:!text-theme-1-100 rounded-md"
                 v-if="element3.hasOwnProperty('elements')">
                {{ element3.label }}
                <svg xmlns="http://www.w3.org/2000/svg" class="size-5 inline-block -rotate-90 ml-2" viewBox="0 0 20 20">
                  <path fill-rule="evenodd"
                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                        clip-rule="evenodd"/>
                </svg>
              </a>
              <div v-if="element3.hasOwnProperty('elements')" class="absolute left-full top-0 bg-white border border-gray-200 rounded-lg shadow-lg z-40 min-w-max hidden group-hover/level-3:block group-focus-within/level-3:block
                          opacity-0 translate-x-2 transition-all duration-200 ease-out group-hover/level-3:opacity-100 group-hover/level-3:translate-x-0 group-focus-within/level-3:opacity-100 group-focus-within/level-3:translate-x-0">
                <ul class="py-2 px-4">
                  <li v-for="element4 in element3.elements" class="relative group/level-4 focus-within:bg-theme-4-750 focus-within:rounded-md focus-within:text-shadow-theme-1-100">
                    <a :href="this.utilsFront.getUrl(element4)" v-if="!element4.hasOwnProperty('elements')"
                       class="block px-4 py-2 hover:bg-theme-4-750 !text-gray-500 hover:!text-theme-1-100 rounded-md">{{ element4.label }}</a>
                  </li>
                </ul>
              </div>
            </li>
          </ul>
        </div>
      </li>
    </ul>
  </div>
</template>