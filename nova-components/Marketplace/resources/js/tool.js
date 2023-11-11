import Tool from './pages/Tool'
import Store from './store/marketplace';

Nova.booting((app, store) => {
  Nova.inertia('Marketplace', Tool);

  store.registerModule('andriichello/marketplace', Store);
})
