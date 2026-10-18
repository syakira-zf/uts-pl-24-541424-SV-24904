import axios from 'axios'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || '/api',
  headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
})

api.interceptors.request.use((config) => {
  const token = localStorage.getItem('syakiratask_token')
  if (token && token !== 'undefined' && token !== 'null') {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

api.interceptors.response.use(
  (response) => {
    // Proteksi sementara terhadap respons Laravel yang salah: HTTP 200
    // tetapi membawa pesan kegagalan autentikasi.
    if (response.data?.message === 'Unauthenticated.') {
      const error = new Error('Unauthorized: API returned an invalid 200 response')
      error.response = { ...response, status: 401 }
      return Promise.reject(error)
    }
    return response
  },
  (error) => {
    if (error.response?.status === 401 && !error.config?.url?.includes('/login')) {
      localStorage.removeItem('syakiratask_token')
      localStorage.removeItem('syakiratask_user')
    }
    return Promise.reject(error)
  },
)

export default api
