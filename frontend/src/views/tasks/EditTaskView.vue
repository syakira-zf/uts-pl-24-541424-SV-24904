
<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import TaskForm from '@/components/TaskForm.vue'
import {
  getTaskById,
  updateTask,
} from '@/services/taskService'
import '@/assets/task-pages.css'

const router = useRouter()
const route = useRoute()

const task = ref(null)
const loading = ref(true)
const saving = ref(false)
const errorMessage = ref('')

async function loadTask() {
  loading.value = true
  errorMessage.value = ''

  try {
    task.value = await getTaskById(route.params.id)
  } catch (error) {
    if (error.response?.status === 401) {
      router.push('/login')
      return
    }

    errorMessage.value =
      error.response?.status === 404
        ? 'Tugas tidak ditemukan.'
        : 'Gagal mengambil data tugas.'
  } finally {
    loading.value = false
  }
}

async function handleUpdate(payload) {
  saving.value = true
  errorMessage.value = ''

  try {
    await updateTask(route.params.id, payload)
    router.push('/tasks')
  } catch (error) {
    if (error.response?.status === 422) {
      const errors = error.response.data.errors || {}

      errorMessage.value =
        Object.values(errors).flat().join(' ') ||
        'Data tidak valid. Periksa kembali form.'
    } else if (error.response?.status === 401) {
      router.push('/login')
    } else {
      errorMessage.value =
        'Gagal memperbarui tugas. Coba lagi.'
    }
  } finally {
    saving.value = false
  }
}

onMounted(loadTask)
</script>

<template>
  <main class="task-page">
    <div class="task-container">
      <RouterLink to="/tasks" class="task-back">
        ← Back to My Tasks
      </RouterLink>

      <div class="task-eyebrow">
        SYAKIRATASK / EDIT ACTIVITY
      </div>

      <h1 class="task-heading">
        Edit Task<span>.</span>
      </h1>

      <p class="task-subtitle">
        Keep your activities organized and up to date.
      </p>

      <section class="task-glass-card">
        <div class="task-eyebrow">
          EDIT EXISTING ACTIVITY
        </div>

        <p v-if="loading" class="task-subtitle">
          Loading task information...
        </p>

        <div v-if="errorMessage" class="task-error" role="alert">
          {{ errorMessage }}
        </div>

        <TaskForm
          v-if="!loading && task"
          :initial-data="task"
          :saving="saving"
          submit-label="Save Changes"
          @submit="handleUpdate"
          @cancel="router.push('/tasks')"
        />
      </section>
    </div>
  </main>
</template>
