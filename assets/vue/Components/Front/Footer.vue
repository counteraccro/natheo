<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Structure de la page de front
 */
export default {
  name: 'Footer',
  props: {
    optionsSystem : Object
  },
  emits: [],
  data() {
    return {
      value: 'Value',
    }
  },
  mounted() {
    console.log(this.optionsSystem)
  },

  methods: {

  }
}
</script>

<template>

    <div class="flex flex-wrap justify-between gap-10">
      <div class="max-w-md">
        <a href='#'>
          {{ this.optionsSystem.OS_SITE_NAME }}
        </a>
        <div class="mt-6">
          <p class="text-slate-600 leading-relaxed text-sm">
            {{ this.optionsSystem.OS_FRONT_FOOTER_TEXTE }}
          </p>
        </div>
        <ul class="mt-10 flex space-x-5">
          <li>
            <a href='javascript:void(0)'>
              <svg xmlns="http://www.w3.org/2000/svg" class="fill-blue-600 w-8 h-8" viewBox="0 0 49.652 49.652">
                <path d="M24.826 0C11.137 0 0 11.137 0 24.826c0 13.688 11.137 24.826 24.826 24.826 13.688 0 24.826-11.138 24.826-24.826C49.652 11.137 38.516 0 24.826 0zM31 25.7h-4.039v14.396h-5.985V25.7h-2.845v-5.088h2.845v-3.291c0-2.357 1.12-6.04 6.04-6.04l4.435.017v4.939h-3.219c-.524 0-1.269.262-1.269 1.386v2.99h4.56z" data-original="#000000" />
              </svg>
            </a>
          </li>
          <li>
            <a href='javascript:void(0)'>
              <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" viewBox="0 0 112.196 112.196">
                <circle cx="56.098" cy="56.097" r="56.098" fill="#007ab9" data-original="#007ab9" />
                <path fill="#fff" d="M89.616 60.611v23.128H76.207V62.161c0-5.418-1.936-9.118-6.791-9.118-3.705 0-5.906 2.491-6.878 4.903-.353.862-.444 2.059-.444 3.268v22.524h-13.41s.18-36.546 0-40.329h13.411v5.715c-.027.045-.065.089-.089.132h.089v-.132c1.782-2.742 4.96-6.662 12.085-6.662 8.822 0 15.436 5.764 15.436 18.149zm-54.96-36.642c-4.587 0-7.588 3.011-7.588 6.967 0 3.872 2.914 6.97 7.412 6.97h.087c4.677 0 7.585-3.098 7.585-6.97-.089-3.956-2.908-6.967-7.496-6.967zm-6.791 59.77H41.27v-40.33H27.865v40.33z" data-original="#f1f2f2" />
              </svg>
            </a>
          </li>
          <li>
            <a href='javascript:void(0)'>
              <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" viewBox="0 0 152 152">
                <linearGradient id="a" x1="22.26" x2="129.74" y1="22.26" y2="129.74" gradientUnits="userSpaceOnUse">
                  <stop offset="0" stop-color="#fae100" />
                  <stop offset=".15" stop-color="#fcb720" />
                  <stop offset=".3" stop-color="#ff7950" />
                  <stop offset=".5" stop-color="#ff1c74" />
                  <stop offset="1" stop-color="#6c1cd1" />
                </linearGradient>
                <g data-name="Layer 2">
                  <g data-name="03.Instagram">
                    <rect width="152" height="152" fill="url(#a)" data-original="url(#a)" rx="76" />
                    <g fill="#fff">
                      <path fill="#ffffff10" d="M133.2 26c-11.08 20.34-26.75 41.32-46.33 60.9S46.31 122.12 26 133.2q-1.91-1.66-3.71-3.46A76 76 0 1 1 129.74 22.26q1.8 1.8 3.46 3.74z" data-original="#ffffff10" />
                      <path d="M94 36H58a22 22 0 0 0-22 22v36a22 22 0 0 0 22 22h36a22 22 0 0 0 22-22V58a22 22 0 0 0-22-22zm15 54.84A18.16 18.16 0 0 1 90.84 109H61.16A18.16 18.16 0 0 1 43 90.84V61.16A18.16 18.16 0 0 1 61.16 43h29.68A18.16 18.16 0 0 1 109 61.16z" data-original="#ffffff" />
                      <path d="m90.59 61.56-.19-.19-.16-.16A20.16 20.16 0 0 0 76 55.33 20.52 20.52 0 0 0 55.62 76a20.75 20.75 0 0 0 6 14.61 20.19 20.19 0 0 0 14.42 6 20.73 20.73 0 0 0 14.55-35.05zM76 89.56A13.56 13.56 0 1 1 89.37 76 13.46 13.46 0 0 1 76 89.56zm26.43-35.18a4.88 4.88 0 0 1-4.85 4.92 4.81 4.81 0 0 1-3.42-1.43 4.93 4.93 0 0 1 3.43-8.39 4.82 4.82 0 0 1 3.09 1.12l.1.1a3.05 3.05 0 0 1 .44.44l.11.12a4.92 4.92 0 0 1 1.1 3.12z" data-original="#ffffff" />
                    </g>
                  </g>
                </g>
              </svg>
            </a>
          </li>
          <li>
            <a href='javascript:void(0)'>
              <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" viewBox="0 0 1227 1227">
                <path d="M613.5 0C274.685 0 0 274.685 0 613.5S274.685 1227 613.5 1227 1227 952.315 1227 613.5 952.315 0 613.5 0z" data-original="#000000" />
                <path fill="#fff" d="m680.617 557.98 262.632-305.288h-62.235L652.97 517.77 470.833 252.692H260.759l275.427 400.844-275.427 320.142h62.239l240.82-279.931 192.35 279.931h210.074L680.601 557.98zM345.423 299.545h95.595l440.024 629.411h-95.595z" data-original="#ffffff" />
              </svg>
            </a>
          </li>
        </ul>
      </div>

      <div class="max-lg:min-w-[140px]">
        <h4 class="text-slate-900 font-semibold text-sm relative max-sm:cursor-pointer">Services</h4>

        <ul class="mt-6 space-y-4">
          <li>
            <a href='javascript:void(0)' class="hover:text-slate-900 text-slate-600 text-sm font-normal">Web Development</a>
          </li>
          <li>
            <a href='javascript:void(0)' class="hover:text-slate-900 text-slate-600 text-sm font-normal">Pricing</a>
          </li>
          <li>
            <a href='javascript:void(0)' class="hover:text-slate-900 text-slate-600 text-sm font-normal">Support</a>
          </li>
          <li>
            <a href='javascript:void(0)' class="hover:text-slate-900 text-slate-600 text-sm font-normal">Client Portal</a>
          </li>
          <li>
            <a href='javascript:void(0)' class="hover:text-slate-900 text-slate-600 text-sm font-normal">Resources</a>
          </li>
        </ul>
      </div>

      <div class="max-lg:min-w-[140px]">
        <h4 class="text-slate-900 font-semibold text-sm relative max-sm:cursor-pointer">Platforms</h4>
        <ul class="space-y-4 mt-6">
          <li>
            <a href='javascript:void(0)' class="hover:text-slate-900 text-slate-600 text-sm font-normal">Hubspot</a>
          </li>
          <li>
            <a href='javascript:void(0)' class="hover:text-slate-900 text-slate-600 text-sm font-normal">Integration Services</a>
          </li>
          <li>
            <a href='javascript:void(0)' class="hover:text-slate-900 text-slate-600 text-sm font-normal">Marketing Glossar</a>
          </li>
          <li>
            <a href='javascript:void(0)' class="hover:text-slate-900 text-slate-600 text-sm font-normal">UIPath</a>
          </li>
        </ul>
      </div>

      <div class="max-lg:min-w-[140px]">
        <h4 class="text-slate-900 font-semibold text-sm relative max-sm:cursor-pointer">Company</h4>

        <ul class="space-y-4 mt-6">
          <li>
            <a href='javascript:void(0)' class="hover:text-slate-900 text-slate-600 text-sm font-normal">About us</a>
          </li>
          <li>
            <a href='javascript:void(0)' class="hover:text-slate-900 text-slate-600 text-sm font-normal">Careers</a>
          </li>
          <li>
            <a href='javascript:void(0)' class="hover:text-slate-900 text-slate-600 text-sm font-normal">Blog</a>
          </li>
          <li>
            <a href='javascript:void(0)' class="hover:text-slate-900 text-slate-600 text-sm font-normal">Portfolio</a>
          </li>
          <li>
            <a href='javascript:void(0)' class="hover:text-slate-900 text-slate-600 text-sm font-normal">Events</a>
          </li>
        </ul>
      </div>

      <div class="max-lg:min-w-[140px]">
        <h4 class="text-slate-900 font-semibold text-sm relative max-sm:cursor-pointer">Additional</h4>

        <ul class="space-y-4 mt-6">
          <li>
            <a href='javascript:void(0)' class="hover:text-slate-900 text-slate-600 text-sm font-normal">FAQ</a>
          </li>
          <li>
            <a href='javascript:void(0)' class="hover:text-slate-900 text-slate-600 text-sm font-normal">Partners</a>
          </li>
          <li>
            <a href='javascript:void(0)' class="hover:text-slate-900 text-slate-600 text-sm font-normal">Sitemap</a>
          </li>
          <li>
            <a href='javascript:void(0)' class="hover:text-slate-900 text-slate-600 text-sm font-normal">Contact</a>
          </li>
          <li>
            <a href='javascript:void(0)' class="hover:text-slate-900 text-slate-600 text-sm font-normal">News</a>
          </li>
        </ul>
      </div>
    </div>

    <hr class="mt-10 mb-6 border-gray-300" />

    <div class="flex flex-wrap max-md:flex-col gap-4">
      <ul class="md:flex md:space-x-6 max-md:space-y-2">
        <li>
          <a href='javascript:void(0)' class="hover:text-slate-900 text-slate-600 text-sm font-normal">Terms of Service</a>
        </li>
        <li>
          <a href='javascript:void(0)' class="hover:text-slate-900 text-slate-600 text-sm font-normal">Privacy Policy</a>
        </li>
        <li>
          <a href='javascript:void(0)' class="hover:text-slate-900 text-slate-600 text-sm font-normal">Security</a>
        </li>
      </ul>

      <p class="text-slate-600 text-sm md:ml-auto">© ReadymadeUI. All rights reserved.</p>
    </div>
</template>