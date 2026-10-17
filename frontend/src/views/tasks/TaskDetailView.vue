
<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { getTaskById } from '@/services/taskService'
import '@/assets/task-pages.css'

const route = useRoute()
const router = useRouter()

const task = ref(null)
const loading = ref(true)
const errorMessage = ref('')

async function loadTask() {
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
        : 'Gagal mengambil detail tugas.'
  } finally {
    loading.value = false
  }
}

function formatDate(value) {
  if (!value) return '-'

  return new Date(
    `${String(value).slice(0, 10)}T00:00:00`,
  ).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  })
}

function formatDeadline(value) {
  if (!value) return '-'

  const normalized = String(value).replace(' ', 'T')
  const date = new Date(normalized)

  if (Number.isNaN(date.getTime())) {
    return String(value)
  }

  return date.toLocaleString('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

function statusLabel(value) {
  return {
    pending: 'To Do',
    in_progress: 'In Progress',
    completed: 'Completed',
  }[value] || value
}

function priorityLabel(value) {
  return {
    low: 'Low',
    medium: 'Medium',
    high: 'High',
  }[value] || value
}

function typeLabel(value) {
  return {
    task: 'Task',
    meeting: 'Meeting',
    event: 'Event',
    study: 'Study',
    personal: 'Personal',
  }[value] || value
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
        SYAKIRATASK / ACTIVITY DETAILS
      </div>

      <h1 class="task-heading">
        Task Details<span>.</span>
      </h1>

      <p class="task-subtitle">
        A closer look at your scheduled activity.
      </p>

      <section class="task-glass-card">
        <p v-if="loading" class="task-subtitle">
          Loading task details...
        </p>

        <div v-else-if="errorMessage" class="task-error">
          {{ errorMessage }}
        </div>

        <template v-else-if="task">
          <div class="task-eyebrow">
            ACTIVITY #{{ task.id }}
          </div>

          <h2
            class="task-section-title"
            style="font-size: 29px; margin-top: 15px"
          >
            {{ task.title }}
          </h2>

          <p class="task-section-description">
            {{ typeLabel(task.type) }} ·
            {{ formatDate(task.activity_date) }}
          </p>

          <div class="task-detail-grid">
            <div class="task-detail-item">
              <div class="task-detail-label">ACTIVITY TYPE</div>
              <div class="task-detail-value">
                {{ typeLabel(task.type) }}
              </div>
            </div>

            <div class="task-detail-item">
              <div class="task-detail-label">ACTIVITY DATE</div>
              <div class="task-detail-value">
                {{ formatDate(task.activity_date) }}
              </div>
            </div>

            <div class="task-detail-item">
              <div class="task-detail-label">START TIME</div>
              <div class="task-detail-value">
                {{ task.start_time?.slice(0, 5) || '-' }}
              </div>
            </div>

            <div class="task-detail-item">
              <div class="task-detail-label">END TIME</div>
              <div class="task-detail-value">
                {{ task.end_time?.slice(0, 5) || '-' }}
              </div>
            </div>

            <div class="task-detail-item">
              <div class="task-detail-label">PRIORITY</div>
              <div class="task-detail-value">
                <span
                  class="task-pill"
                  :class="{ dark: task.priority === 'high' }"
                >
                  {{ priorityLabel(task.priority) }}
                </span>
              </div>
            </div>

            <div class="task-detail-item">
              <div class="task-detail-label">STATUS</div>
              <div class="task-detail-value">
                <span
                  class="task-pill"
                  :class="{ dark: task.status === 'in_progress' }"
                >
                  {{ statusLabel(task.status) }}
                </span>
              </div>
            </div>

            <div class="task-detail-item full">
              <div class="task-detail-label">DEADLINE</div>
              <div class="task-detail-value">
                {{ formatDeadline(task.deadline) }}
              </div>
            </div>

            <div class="task-detail-item full">
              <div class="task-detail-label">DESCRIPTION</div>
              <div class="task-detail-value description">
                {{ task.description || 'No description available.' }}
              </div>
            </div>
          </div>

          <div class="task-actions">
            <RouterLink
              to="/tasks"
              class="task-btn task-btn-secondary"
            >
              Back to Tasks
            </RouterLink>

            <RouterLink
              :to="`/tasks/${task.id}/edit`"
              class="task-btn task-btn-primary"
            >
              Edit Task ↗
            </RouterLink>
          </div>
        </template>
      </section>
    </div>
  </main>
</template>

<style scoped>
.task-detail-item.full {
  grid-column: 1 / -1;
}
</style>
