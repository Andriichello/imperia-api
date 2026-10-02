<script setup lang="ts">
  import {ref, onMounted, onUnmounted, watch, computed} from 'vue'
  import type {DishCategory, DishMenu, Dish, Restaurant} from '@/api'
  import Deferred from '@/Components/Deferred.vue'
  import NavBar from '@/Components/Base/NavBar.vue'
  import SearchDrawer from '@/Components/Drawer/SearchDrawer.vue'
  import LanguageDrawer from '@/Components/Drawer/LanguageDrawer.vue'
  import {switchLanguage} from '@/i18n/utils'
  import {useI18n} from 'vue-i18n'
  import BaseLayout from '@/Layouts/BaseLayout.vue'
  import DiagonalPattern from '@/Components/Base/DiagonalPattern.vue'
  import RestaurantComponent from '@/Components/Preview/RestaurantComponent.vue'
  import MenuInList from '@/Components/Menu/MenuInList.vue'
  import MenuNavBar from '@/Components/Menu/MenuNavBar.vue'
  import CategoryNavBar from '@/Components/Menu/CategoryNavBar.vue'
  import LoadingMenuInList from '@/Components/Menu/LoadingMenuInList.vue'
  import ProductDrawer from '@/Components/Drawer/ProductDrawer.vue'
  import {useAppStore} from '@/stores/app'
  import {usePreviewStore} from '@/stores/preview'

  // Stores & shared props from Blade
  const app = useAppStore()
  const restaurant = computed<Restaurant>(() => app.restaurant as Restaurant)
  const menus = computed<DishMenu[]>(() => app.menus || [])
  const locale = computed(() => app.locale)
  const supported_locales = computed(() => app.supported_locales)

  const i18n = useI18n();

  // Preview store (products)
  const preview = usePreviewStore()
  const productsFailed = computed(() => preview.error)

  // Dishes are shown only in a visible category of a visible menu, so the
  // ones without a category or in a hidden one are left out (search included)
  const visibleCategoryIds = computed<Set<number>>(
    () => new Set(menus.value.flatMap((m: DishMenu) => (m.categories ?? []).map((c: DishCategory) => c.id)))
  )
  const products = computed<Dish[] | null>(
    () => preview.products?.filter((p: Dish) => visibleCategoryIds.value.has(p.category_id as number)) ?? null
  )

  function reloadProducts() {
    preview.loadProducts()
  }

  const isSearchOpened = ref(false)
  const isLanguageOpened = ref(false)
  const isProductOpened = ref(false)

  const isSearchWithAutofocus = ref(true)

  const mode = ref<string>(window.location.pathname.includes('/menu') ? 'menu' : 'restaurant')

  // Resolve ids from URL within the /{locale}/web base
  function resolveRestaurantId() {
    // We are mounted under /{locale}/web base. Extract the first number after base.
    const parts = window.location.pathname.split('/')
    // e.g. ['', 'en', 'web', '123', 'menu', '5']
    const idx = parts.indexOf('web')
    const idPart = idx !== -1 ? parts[idx + 1] : parts[1]
    const id = parseInt(idPart || '')
    return Number.isFinite(id) ? id : null
  }

  function resolveMenuId() {
    const match = window.location.pathname.match(/\/menu\/(\d+)/)
    return match ? parseInt(match[1]) : null
  }

  function resolveCategoryId() {
    let categoryId: any = window.location.hash.replace('#', '')

    if (categoryId.includes('-')) {
      const parts = categoryId.split('-')
      categoryId = parts[0]
    }

    return categoryId?.length > 0 ? parseInt(categoryId) : null
  }

  function resolveProductId() {
    let categoryId = window.location.hash.replace('#', '')
    let productId: any = null

    if (categoryId.includes('-')) {
      const parts = categoryId.split('-')
      productId = parts[1]
    }

    return productId?.length > 0 ? parseInt(productId) : null
  }

  function resolveAllIds() {
    const resolvedRestaurantId = resolveRestaurantId()

    if (resolvedRestaurantId !== restaurantId.value) {
      restaurantId.value = resolvedRestaurantId as number
    }

    const resolvedMenuId = resolveMenuId()

    if (resolvedMenuId !== menuId.value) {
      menuId.value = resolvedMenuId
      selectedMenu.value = findMenu(resolvedMenuId)
    }

    const resolvedCategoryId = resolveCategoryId()

    if (resolvedCategoryId !== categoryId.value) {
      categoryId.value = resolvedCategoryId
      selectedCategory.value = findCategory(resolvedCategoryId)
    }

    const resolvedProductId = resolveProductId()

    if (resolvedProductId !== productId.value) {
      productId.value = resolvedProductId
      selectedProduct.value = findProduct(resolvedProductId)
    }
  }

  const restaurantId = ref<number | null>(null)
  const menuId = ref<number | null>(null)
  const categoryId = ref<number | null>(null)
  const productId = ref<number | null>(null)

  function findMenu(menuIdLocal: string | number | null) {
    if (menuIdLocal !== null) {
      return (menus.value || []).find((m: DishMenu) => Number(m.id) === menuIdLocal)
    }
    return null
  }

  function findCategory(categoryIdLocal: string | number | null) {
    if (categoryIdLocal !== null) {
      for (const menu of menus.value || []) {
        for (const category of menu.categories || []) {
          if (
            (category.id === Number(categoryIdLocal)) ||
            (category.slug === String(categoryIdLocal))
          ) {
            return category
          }
        }
      }
    }
    return null
  }

  function findProduct(productIdLocal: string | number | null) {
    if (productIdLocal !== null) {
      for (const product of products.value ?? []) {
        if (product.id === Number(productIdLocal)) {
          return product
        }
      }
    }
    return null
  }

  const selectedMenu = ref<DishMenu | null>(null)
  const selectedCategory = ref<DishCategory | null>(null)
  const selectedProduct = ref<Dish | null>(null)

  // Navigation & History helpers
  type HistoryAction = 'push' | 'replace'

  function getBasePath(): string {
    return window.location.pathname.split('/menu/')[0]
  }

  function buildUrl(mId: number | null, cId: number | null, pId: number | null, isPage: boolean): string {
    const base = getBasePath()
    if (!mId) {
      return base // restaurant mode
    }
    let url = `${base}/menu/${mId}`
    if (cId) {
      url += `#${cId}`
      if (pId) {
        url += `-${pId}`
        if (isPage) {
          url += `-page`
        }
      }
    }
    return url
  }

  function setHistory(action: HistoryAction, state: any): void {
    const fn = action === 'push' ? window.history.pushState : window.history.replaceState
    try {
      fn.call(window.history, state, '', buildUrl(state.menuId ?? null, state.categoryId ?? null, state.productId ?? null, !!state.productPage))
    } catch (_) {
      // Fallback to replaceState without URL change if something goes wrong
      window.history.replaceState(state, '')
    }
  }

  const updateHistoryScroll = () => {
    const current = window.history.state || {}
    const next = { ...current, scrollY: window.scrollY }
    window.history.replaceState(next, '')
  }

  const switchMenu = (menu: DishMenu, historyAction: HistoryAction = 'replace') => {
    // Only update if it's a different menu
    if (historyAction === 'push' || menu.id !== selectedMenu.value?.id) {
      ignoringScroll.value = true

      window.scrollTo({top: 0, behavior: 'smooth'})

      // Update component state locally
      menuId.value = menu.id as number
      selectedMenu.value = menu
      categoryId.value = null
      selectedCategory.value = null
      productId.value = null
      selectedProduct.value = null

      // Update history
      setHistory(historyAction, {
        mode: 'menu',
        restaurantId: restaurantId.value ?? resolveRestaurantId(),
        menuId: menu.id,
        categoryId: null,
        productId: null,
        productPage: false,
        scrollY: 0,
      })

      const idToCheck = ignoringScrollId.value++

      setTimeout(() => {
        if (idToCheck === (ignoringScrollId.value - 1)) {
          ignoringScroll.value = false
        }
      }, 200)

    }
  }

  const switchCategory = (category: DishCategory, product: Dish | null = null, historyAction: HistoryAction = 'replace') => {
    // Only update if it's a different category or we explicitly want to push
    if (historyAction === 'push' || category.id !== selectedCategory.value?.id || (product?.id !== selectedProduct.value?.id)) {
      // Update component state locally
      categoryId.value = category.id
      selectedCategory.value = category
      productId.value = product?.id ?? null
      selectedProduct.value = product ?? null

      // Update the URL in the browser without a page reload
      setHistory(historyAction, {
        mode: 'menu',
        restaurantId: restaurantId.value ?? resolveRestaurantId(),
        menuId: menuId.value ?? selectedMenu.value?.id ?? null,
        categoryId: category.id,
        productId: product?.id ?? null,
        productPage: false,
        scrollY: window.scrollY,
      })
    }
  }

  const stickyRef = ref<HTMLElement | null>(null)

  const ignoringScroll = ref(false)
  const ignoringScrollId = ref(0)

  const shouldNotScroll = ref(Date.now())
  const lastScrollPosition = ref(0)
  const continuousScroll = ref(0)
  const continuousScrollAt = ref<number | null>(null)
  const scrolledToSticky = ref(false)

  const showGoToTop = ref(false)
  const isGoingToTop = ref(false)

  // Height of the nav row (back, language, search) above the menus and categories
  const NAV_HEIGHT = 60

  const goToTop = () => {
    showGoToTop.value = false
    ignoringScroll.value = true
    isGoingToTop.value = true

    selectedCategory.value = null

    window.scrollTo({
      top: 0,
      behavior: 'smooth'
    })

    const idToCheck = ignoringScrollId.value++

    setTimeout(() => {
      isGoingToTop.value = false

      if (idToCheck === (ignoringScrollId.value - 1)) {
        ignoringScroll.value = false
      }
    }, 1000)
  }

  const onScroll = () => {
    // Get the current scroll position
    const scrollPosition = window.pageYOffset || document.documentElement.scrollTop

    if (scrollPosition > NAV_HEIGHT) {
      if (!scrolledToSticky.value) {
        scrolledToSticky.value = true
        showGoToTop.value = false
      }
    } else {
      if (scrolledToSticky.value) {
        scrolledToSticky.value = false
      }
    }

    // Because of momentum scrolling on mobiles, we shouldn't continue if it is less than zero
    if (scrollPosition < 0) {
      lastScrollPosition.value = scrollPosition
    }

    if (ignoringScroll.value) {
      // Ignore programmatic scrolls (e.g., closing drawers, auto scrolls)
      lastScrollPosition.value = scrollPosition
      continuousScroll.value = 0
      continuousScrollAt.value = null
      showGoToTop.value = false
      return
    }

    const isScrollingUp = (lastScrollPosition.value - scrollPosition) > 0
    const isScrollingDown = (scrollPosition - lastScrollPosition.value) > 0

    if (isScrollingUp) {
      if (!continuousScrollAt.value || continuousScrollAt.value > 0) {
        continuousScrollAt.value = -Date.now()
      }

      if (continuousScroll.value < 0) {
        continuousScroll.value = 0
        continuousScrollAt.value = -Date.now()
      }

      continuousScroll.value += lastScrollPosition.value - scrollPosition
    }

    if (isScrollingDown) {
      if (!continuousScrollAt.value || continuousScrollAt.value < 0) {
        continuousScrollAt.value = Date.now()
      }

      if (continuousScroll.value > 0) {
        continuousScroll.value = 0
        continuousScrollAt.value = Date.now()
      }

      continuousScroll.value -= scrollPosition - lastScrollPosition.value
    }

    const scrollAt = Math.abs(continuousScrollAt.value ?? 0)

    if (isScrollingUp && scrollAt && (Date.now() - scrollAt) < 500) {
      if (continuousScroll.value > 1000) {
        showGoToTop.value = true
      }
    } else {
      if (continuousScroll.value < -100 || scrollPosition < 120) {
        showGoToTop.value = false
      }

      continuousScrollAt.value = null

      if (isScrollingUp) {
        continuousScroll.value = lastScrollPosition.value - scrollPosition
      }
    }

    if (scrollPosition < 0 || ignoringScroll.value) {
      return
    }

    // Pause auto-selection while any drawer is open or scroll is disabled
    if (isSearchOpened.value || isLanguageOpened.value || isProductOpened.value) {
      lastScrollPosition.value = scrollPosition
      return
    }

    const categoriesCount = selectedMenu.value?.categories?.length ?? 0

    for (let i = 0; i < categoriesCount; i++) {
      const category = selectedMenu.value!.categories[i]
      const group = document.getElementById(`category-${category.id}`)

      if (!group) {
        continue
      }

      const stickyHeight = stickyRef.value?.clientHeight ?? 96

      const isTopOutOfView = group.offsetTop < scrollPosition
      const isBottomOutOfView = (group.offsetTop + group.clientHeight - stickyHeight) < scrollPosition

      if (!isTopOutOfView || !isBottomOutOfView) {
        shouldNotScroll.value = Date.now()

        // select a category and scroll to it...
        switchCategory(category, null, 'replace')
        break
      }
    }

    lastScrollPosition.value = scrollPosition

    // keep current scroll in history for restoration
    if (!ignoringScroll.value) {
      updateHistoryScroll()
    }
  }

  const scrollToCategory = (category: DishCategory, product: Dish | null = null) => {
    const divider = document.getElementById('category-' + category.id)

    if ((shouldNotScroll.value === 0 || (Date.now() - shouldNotScroll.value) > 100) && divider) {
      ignoringScroll.value = true

      let top = divider.getBoundingClientRect().top

      const stickyHeight = stickyRef.value?.clientHeight ?? 96

      const productElement = product ? document.getElementById('product-' + product.id) : null

      if (productElement) {
        top = productElement.getBoundingClientRect().top
      }

      window.scrollTo({
        top: top + window.pageYOffset - stickyHeight - 10,
        behavior: 'smooth'
      })

      const idToCheck = ignoringScrollId.value++

      setTimeout(() => {
        if (idToCheck === (ignoringScrollId.value - 1)) {
          ignoringScroll.value = false
        }
      }, 1000)
    }

    shouldNotScroll.value = 0
  }

  const onSwitchMenu = (menu: DishMenu) => {
    if (mode.value !== 'menu') {
      mode.value = 'menu'
    }

    isSearchOpened.value = false

    switchMenu(menu, 'push')
  }

  const onSwitchCategory = (category: DishCategory, menu: DishMenu = selectedMenu.value as DishMenu) => {
    if (mode.value !== 'menu') {
      mode.value = 'menu'
    }

    isSearchOpened.value = false

    if (menu.id !== selectedMenu.value?.id) {
      switchMenu(menu, 'replace')
    }

    setTimeout(() => {
      switchCategory(category, null, 'push')
      scrollToCategory(category)
    }, 200)
  }

  const onSwitchProduct = (product: Dish, category: DishCategory, menu: DishMenu = selectedMenu.value as DishMenu) => {
    if (mode.value !== 'menu') {
      mode.value = 'menu'
    }

    isSearchOpened.value = false

    if (menu.id !== selectedMenu.value?.id) {
      switchMenu(menu, 'replace')
    }

    setTimeout(() => {
      switchCategory(category, product, 'push')
      scrollToCategory(category, product)
    }, 200)
  }

  function onBackFromMenu() {
    mode.value = 'restaurant'

    // Clear selections
    categoryId.value = null
    selectedCategory.value = null
    productId.value = null
    selectedProduct.value = null

    // Push restaurant state into history
    setHistory('push', {
      mode: 'restaurant',
      restaurantId: restaurantId.value ?? resolveRestaurantId(),
      menuId: null,
      categoryId: null,
      productId: null,
      productPage: false,
      scrollY: 0,
    })
  }

  // Methods for Drawers
  function onOpenSearch() {
    isSearchOpened.value = true
    isSearchWithAutofocus.value = true

    if (productsFailed.value) {
      reloadProducts()
    }
  }

  function onOpenLanguage() {
    isLanguageOpened.value = true
  }

  //  Event handlers for Drawers
  const onOpenMenu = (menu: DishMenu) => {
    isSearchOpened.value = false
    mode.value = 'menu'

    switchMenu(menu, 'push')
  }

  const onOpenCategory = (category: DishCategory, menu: DishMenu | null = null) => {
    isSearchOpened.value = false

    menu = menu ?? findMenu(menuId.value)

    if (menu && menu.id !== selectedMenu.value?.id) {
      switchMenu(menu, 'replace')
    }

    switchCategory(category, null, 'push')
  }

  const onOpenProduct = ({product, category, menu}: { product: Dish, category: DishCategory, menu: DishMenu}) => {
    isSearchOpened.value = false

    // Update selection
    menuId.value = menu.id as number
    categoryId.value = category.id
    productId.value = product.id

    selectedMenu.value = menu
    selectedCategory.value = category
    selectedProduct.value = product

    // Ensure we have a product state (#categoryId-productId) before pushing the page state
    const hash = window.location.hash || ''
    const currentCategoryId = resolveCategoryId()
    const currentProductId = resolveProductId()
    const isCurrentlyPage = hash.includes('-page')

    // If we are not already on the product-only state for this product, push it
    if (!(currentCategoryId === category.id && currentProductId === product.id && !isCurrentlyPage)) {
      setHistory('push', {
        mode: 'menu',
        restaurantId: restaurantId.value ?? resolveRestaurantId(),
        menuId: menu.id,
        categoryId: category.id,
        productId: product.id,
        productPage: false,
        scrollY: window.scrollY,
      })
    }

    // Now push product page state into history (adds -page hash)
    if (!(currentCategoryId === category.id && currentProductId === product.id && isCurrentlyPage)) {
      setHistory('push', {
        mode: 'menu',
        restaurantId: restaurantId.value ?? resolveRestaurantId(),
        menuId: menu.id,
        categoryId: category.id,
        productId: product.id,
        productPage: true,
        scrollY: window.scrollY,
      })
    }

    isProductOpened.value = true
  }

  const onCloseProduct = () => {
    // Close the drawer without triggering a browser back navigation to avoid page reload
    isProductOpened.value = false

    const rId = restaurantId.value ?? resolveRestaurantId()
    const mId = menuId.value ?? selectedMenu.value?.id ?? null
    const cId = categoryId.value ?? selectedCategory.value?.id ?? null
    const pId = productId.value ?? selectedProduct.value?.id ?? null

    if (mId && cId) {
      // Replace current URL to product-only state (#categoryId-productId) by removing the "-page" suffix
      setHistory('replace', {
        mode: 'menu',
        restaurantId: rId,
        menuId: mId,
        categoryId: cId,
        productId: pId ?? null,
        productPage: false,
        scrollY: window.scrollY,
      })
    } else if (mId) {
      // Fallback to menu top if category is missing
      setHistory('replace', {
        mode: 'menu',
        restaurantId: rId,
        menuId: mId,
        categoryId: null,
        productId: null,
        productPage: false,
        scrollY: window.scrollY,
      })
    } else {
      // Fallback to restaurant mode
      setHistory('replace', {
        mode: 'restaurant',
        restaurantId: rId,
        menuId: null,
        categoryId: null,
        productId: null,
        productPage: false,
        scrollY: window.scrollY,
      })
    }
  }

  const onSwitchLanguage = (l: string) => {
    switchLanguage(i18n, l)
  }

  function applyStateFromUrl(state: any = window.history.state) {
    // Parse IDs from URL
    resolveAllIds()

    // Determine mode from path
    mode.value = window.location.pathname.includes('/menu') ? 'menu' : 'restaurant'

    // Ensure menu aligns with category if needed
    if (!selectedMenu.value && menuId.value) {
      selectedMenu.value = findMenu(menuId.value) ?? selectedMenu.value
    }
    if (!selectedMenu.value && selectedCategory.value?.menu?.id) {
      selectedMenu.value = findMenu(selectedCategory.value.menu.id) ?? selectedMenu.value
    }

    // Drawer state based on hash suffix
    const hash = window.location.hash || ''
    const isPage = hash.includes('-page')
    if (isPage && selectedProduct.value) {
      isProductOpened.value = true
    } else {
      isProductOpened.value = false
    }

    // Restore scroll position
    const desiredY = typeof state?.scrollY === 'number' ? state.scrollY : null
    const category = selectedCategory.value ?? (categoryId.value ? findCategory(categoryId.value) : null)

    if (desiredY !== null) {
      ignoringScroll.value = true
      window.scrollTo({ top: desiredY })
      const idToCheck = ignoringScrollId.value++
      setTimeout(() => {
        if (idToCheck === (ignoringScrollId.value - 1)) {
          ignoringScroll.value = false
        }
      }, 100)
      return
    }

    if (category) {
      shouldNotScroll.value = 0
      ignoringScroll.value = false
      scrollToCategory(category, selectedProduct.value ?? null)
    } else {
      setTimeout(goToTop, 100)
    }
  }

  function onPopState(e: PopStateEvent) {
    // Close overlay drawers on browser navigation
    isSearchOpened.value = false
    isLanguageOpened.value = false
    applyStateFromUrl(e.state)
  }

  onMounted(async () => {
    window.addEventListener('scroll', onScroll)
    window.addEventListener('popstate', onPopState)

    resolveAllIds()

    // Initialize the history state for the current entry
    const initialState = {
      mode: window.location.pathname.includes('/menu') ? 'menu' : 'restaurant',
      restaurantId: restaurantId.value ?? resolveRestaurantId(),
      menuId: menuId.value ?? selectedMenu.value?.id ?? null,
      categoryId: categoryId.value ?? selectedCategory.value?.id ?? null,
      productId: productId.value ?? selectedProduct.value?.id ?? null,
      productPage: (window.location.hash || '').includes('-page'),
      scrollY: window.scrollY,
    }
    window.history.replaceState(initialState, '', buildUrl(initialState.menuId, initialState.categoryId, initialState.productId, initialState.productPage))

    // Load all products for all menus initially via store
    const allMenuIds = (menus.value || []).map((m: any) => Number(m.id)).filter((id: any) => Number.isFinite(id)) as number[]
    const rId = restaurantId.value ?? resolveRestaurantId()
    preview.setContext(rId as number | null, null, allMenuIds)
    await preview.loadProducts({ restaurantId: rId as number | null, menuIds: allMenuIds })

    // Open Product drawer automatically if the URL hash targets a product page
    const hash = window.location.hash || ''
    if (hash.includes('-page') && selectedProduct.value) {
      isProductOpened.value = true
    }
  })

  onUnmounted(() => {
    window.removeEventListener('scroll', onScroll)
    window.removeEventListener('popstate', onPopState)
  })

  // React to products initial load (mirrors original behavior)
  watch(products, (newValue, oldValue) => {
    if (oldValue) {
      return
    }

    let hashVal: any = window.location.hash.replace('#', '')
    let productIdLocal: number | string | null = null

    const isProductPage = hashVal.includes('-page')

    if (hashVal.includes('-')) {
      const parts = hashVal.split('-')

      hashVal = parts[0]
      productIdLocal = parts[1]
    }

    const category = findCategory(hashVal)
    const product = findProduct(productIdLocal)

    if (category) {
      // Ensure the menu aligns with the category
      if (!selectedMenu.value || selectedMenu.value.id !== category.menu?.id) {
        selectedMenu.value = findMenu(menuId.value) ?? category.menu ?? selectedMenu.value
      }

      selectedCategory.value = category

      if (product) {
        selectedProduct.value = product
        productId.value = product.id
      }

      setTimeout(() => {
        if (selectedMenu.value?.categories?.[0]?.id !== category.id || product) {
          shouldNotScroll.value = 0
          ignoringScroll.value = false
          scrollToCategory(category, product)
        }

        if (isProductPage && product) {
          isProductOpened.value = true
        }
      }, 100)
    } else {
      setTimeout(() => {
        goToTop()
      }, 100)
    }
  })

  // When the search or language drawer closes, ignore scroll events briefly to prevent auto-selection
  watch(() => isSearchOpened.value || isLanguageOpened.value, (isOpen, wasOpen) => {
    if (wasOpen && !isOpen) {
      ignoringScroll.value = true
      const idToCheck = ignoringScrollId.value++
      setTimeout(() => {
        if (idToCheck === (ignoringScrollId.value - 1)) {
          ignoringScroll.value = false
        }
      }, 500)
    }
  })

  // When product drawer closes, BaseDrawer restores scroll position which can trigger onScroll.
  // Pause auto-selection briefly when it closes and reset tracking.
  watch(() => isProductOpened.value, (newValue, oldValue) => {
    if (oldValue === true && newValue === false) {
      // Short-circuit onScroll and reset scroll tracking so Go To Top doesn't flicker
      ignoringScroll.value = true
      lastScrollPosition.value = window.pageYOffset || document.documentElement.scrollTop || 0
      continuousScroll.value = 0
      continuousScrollAt.value = null
      showGoToTop.value = false

      const idToCheck = ignoringScrollId.value++
      setTimeout(() => {
        if (idToCheck === (ignoringScrollId.value - 1)) {
          ignoringScroll.value = false
        }
      }, 500)
    }
  })

</script>

<template>
  <BaseLayout>
    <div class="w-full max-w-md flex flex-col justify-center items-center">
      <template v-if="mode === 'restaurant'">
        <div class="w-full max-w-md absolute top-0 h-75 bg-base-200/20 border-b-1 border-base-300 overflow-hidden flex flex-col justify-center">
          <DiagonalPattern class="scale-165 text-primary-content/50"
                           :establishment="restaurant?.establishment ?? 'restaurant'"/>
        </div>

        <RestaurantComponent :restaurant="restaurant"
                             :menus="menus"
                             @open-menu="onOpenMenu"/>
      </template>

      <template v-else>
        <div class="w-full max-w-md flex flex-col justify-start items-center relative">
          <!-- Nav row at the top of the page; only the menus and categories stick -->
          <div class="w-full p-2">
            <NavBar :back="true"
                    @on-back="onBackFromMenu"
                    @on-search="onOpenSearch"
                    @on-language="onOpenLanguage"/>
          </div>

          <div class="w-full sticky top-0 z-10 bg-base-100 border-y border-base-300"
               ref="stickyRef"
               :class="{'shadow-md': scrolledToSticky}"
               v-if="products || !productsFailed">
            <Deferred :data="products">
              <template #fallback>
                <div class="w-full min-h-[92px] flex flex-col justify-center items-center">
                  <div class="loading loading-dots loading-lg text-primary/40"/>
                </div>
              </template>

              <MenuNavBar class="w-full"
                          :menus="menus"
                          :selected="selectedMenu"
                          @switch-menu="onSwitchMenu"
                          @open-drawer="isSearchWithAutofocus = false; isSearchOpened = true;"/>

              <CategoryNavBar class="w-full"
                              :categories="selectedMenu?.categories ?? []"
                              :selected="selectedCategory"
                              @switch-category="onSwitchCategory"/>

              <transition name="go-to-top">
                <button class="text-sm px-2 py-1 font-semibold rounded-sm flex justify-center items-center absolute left-[50%] translate-x-[-50%] top-full mt-2 z-10 backdrop-blur-sm bg-neutral/35 text-white border-none uppercase cursor-pointer"
                        v-if="showGoToTop && !isGoingToTop && scrolledToSticky"
                        @click="goToTop">
                  {{ i18n.t('menu.go_to_top') }}
                </button>
              </transition>
            </Deferred>
          </div>

          <!-- Menus list -->
          <Deferred :data="products">
            <template #fallback>
              <div class="w-full flex flex-col justify-center items-center gap-4 px-6 py-16 text-center"
                   v-if="productsFailed">
                <p class="text-lg text-base-content/80">
                  {{ i18n.t('menu.loading_failed') }}
                </p>

                <button class="btn btn-sm"
                        @click="reloadProducts">
                  {{ i18n.t('menu.try_again') }}
                </button>
              </div>

              <LoadingMenuInList v-else/>
            </template>

            <div class="w-full flex flex-col">
              <MenuInList :menu="selectedMenu ?? null"
                          :products="products ?? []"
                          :closed="false"
                          :currency="restaurant?.currency ?? 'uah'"
                          :establishment="restaurant?.establishment ?? 'restaurant'"
                          @switch-menu="onSwitchMenu"
                          @switch-category="onSwitchCategory"
                          @open-product="({product, category, menu}) => onOpenProduct({product, category, menu: menu ?? selectedMenu})"/>
            </div>
          </Deferred>
        </div>
      </template>

      <div class="min-h-13 w-full max-w-md absolute top-0 p-2"
           v-if="mode === 'restaurant'">
        <NavBar @on-search="onOpenSearch"
                @on-language="onOpenLanguage"/>
      </div>

      <SearchDrawer :open="isSearchOpened"
                    :restaurant="restaurant"
                    :menus="menus"
                    :products="products"
                    :with-autofocus="isSearchWithAutofocus"
                    @close="isSearchOpened = false"
                    @open-menu="onSwitchMenu"
                    @open-category="onSwitchCategory"
                    @open-product="onSwitchProduct"
                    @open-language="onOpenLanguage"/>

      <LanguageDrawer :open="isLanguageOpened"
                      :locale="locale"
                      :supported_locales="supported_locales"
                      @close="isLanguageOpened = false"
                      @switch-language="onSwitchLanguage"/>

      <ProductDrawer :open="isProductOpened"
                     :product="selectedProduct"
                     :currency="restaurant?.currency ?? 'uah'"
                     :establishment="restaurant?.establishment ?? 'restaurant'"
                     @close="onCloseProduct"/>
    </div>
  </BaseLayout>
</template>

<style scoped>
  .go-to-top-enter-active,
  .go-to-top-leave-active {
    transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.25s cubic-bezier(0.4, 0, 0.2, 1);
  }

  .go-to-top-enter-from,
  .go-to-top-leave-to {
    transform: translateY(-40%);
    opacity: 0;
  }
</style>
