
import api from './api'

// READ - Menampilkan seluruh tugas
export async function getTasks() {
  const response = await api.get('/tasks')
  return response.data.data
}

// READ - Menampilkan detail satu tugas
export async function getTaskById(id) {
  const response = await api.get(`/tasks/${id}`)
  return response.data.data
}

// CREATE - Menambahkan tugas baru
export async function createTask(data) {
  const response = await api.post('/tasks', data)
  return response.data.data
}

// UPDATE - Mengubah tugas
export async function updateTask(id, data) {
  const response = await api.put(`/tasks/${id}`, data)
  return response.data.data
}

// DELETE - Menghapus tugas
export async function deleteTask(id) {
  const response = await api.delete(`/tasks/${id}`)
  return response.data
}
