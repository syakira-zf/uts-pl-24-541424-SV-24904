
<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import TaskForm from '@/components/TaskForm.vue'
import { createTask } from '@/services/taskService'
import '@/assets/task-pages.css'

const router = useRouter()

const saving = ref(false)
const errorMessage = ref('')

async function handleCreate(payload) {
  saving.value = true
  errorMessage.value = ''

  try {
    await createTask(payload)

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
        'Gagal menambahkan tugas. Periksa koneksi Laravel.'
    }
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <main class="task-page">
    <div class="task-container">
      <RouterLink to="/tasks" class="task-back">
        ← Back to My Tasks
      </RouterLink>

      <div class="task-eyebrow">
        SYAKIRATASK / NEW ACTIVITY
      </div>

      <h1 class="task-heading">
        Create Task<span>.</span>
      </h1>

      <p class="task-subtitle">
        A clear plan is the first step to a productive day.
      </p>

      <section class="task-glass-card">
        <div class="task-eyebrow">
          NEW TASK / AGENDA
        </div>

        <div v-if="errorMessage" class="task-error" role="alert">
          {{ errorMessage }}
        </div>

        <TaskForm
          :saving="saving"
          submit-label="Create Task"
          @submit="handleCreate"
          @cancel="router.push('/tasks')"
        />
      </section>
    </div>
  </main>
</template>
