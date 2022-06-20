Nova.booting((Vue, router, store) => {
  router.addRoutes([
    {
      name: 'marketplace',
      path: '/marketplace',
      component: require('./components/Tool').default,
    },
  ])
})
