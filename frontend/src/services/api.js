
import axios from 'axios'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || '/api',
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
})

// Token otomatis dikirim pada setiap request
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('syakiratask_token')

  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }

  return config
})

// Jika token tidak valid, hapus token lama
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (
      error.response?.status === 401 &&
      !error.config?.url?.includes('/login')
    ) {
      localStorage.removeItem('syakiratask_token')
      localStorage.removeItem('syakiratask_user')
    }

    return Promise.reject(error)
  },
)

export default api
