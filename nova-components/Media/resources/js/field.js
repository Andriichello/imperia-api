import IndexField from './components/IndexField'
import DetailField from './components/DetailField'
import FormField from './components/FormField'

Nova.booting((app, store) => {
  app.component('index-media-field', IndexField)
  app.component('detail-media-field', DetailField)
  app.component('form-media-field', FormField)
})
