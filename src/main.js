import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import axios from 'axios'
import "bootstrap/dist/css/bootstrap.css" ;  // bootstrap 5 css
import "bootstrap/dist/js/bootstrap.bundle.js" ;  // bootstrap 5 javaScript
// ตั้ง default headers axios
axios.defaults.headers.common['Content-Type'] = 'application/json'

const app = createApp(App)
app.use(router)
app.mount('#app')
