
<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { getTasks, deleteTask } from '@/services/taskService'

const router = useRouter()

const tasks = ref([])
const loading = ref(true)
const deletingId = ref(null)
const errorMessage = ref('')
const successMessage = ref('')
const search = ref('')
const statusFilter = ref('all')

const filteredTasks = computed(() => {
  return tasks.value.filter((task) => {
    const keyword = search.value.toLowerCase()

    const matchesSearch =
      task.title.toLowerCase().includes(keyword) ||
      (task.description || '').toLowerCase().includes(keyword)

    const matchesStatus =
      statusFilter.value === 'all' ||
      task.status === statusFilter.value

    return matchesSearch && matchesStatus
  })
})

async function loadTasks() {
  loading.value = true
  errorMessage.value = ''

  try {
    tasks.value = await getTasks()
  } catch (error) {
    if (error.response?.status === 401) {
      router.push('/login')
      return
    }

    errorMessage.value = 'Gagal mengambil data tugas dari Laravel.'
  } finally {
    loading.value = false
  }
}

async function handleDelete(task) {
  const confirmed = window.confirm(
    `Apakah kamu yakin ingin menghapus "${task.title}"?`,
  )

  if (!confirmed) return

  deletingId.value = task.id
  errorMessage.value = ''
  successMessage.value = ''

  try {
    await deleteTask(task.id)
    successMessage.value = 'Tugas berhasil dihapus.'
    await loadTasks()
  } catch (error) {
    if (error.response?.status === 401) {
      router.push('/login')
      return
    }

    errorMessage.value = 'Gagal menghapus tugas.'
  } finally {
    deletingId.value = null
  }
}

function formatDate(value) {
  if (!value) return '-'

  const date = new Date(`${String(value).slice(0, 10)}T00:00:00`)

  return date.toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  })
}

function statusLabel(status) {
  return {
    pending: 'To Do',
    in_progress: 'In Progress',
    completed: 'Completed',
  }[status] || status
}

function priorityLabel(priority) {
  return {
    low: 'Low',
    medium: 'Medium',
    high: 'High',
  }[priority] || priority
}

onMounted(loadTasks)
</script>

<template>
  <div class="tasks-page">
    <header class="page-header">
      <div>
        <div class="eyebrow">SYAKIRATASK / PERSONAL WORKSPACE</div>
        <h1>My Tasks<span>.</span></h1>
        <p>Everything you need to organize your day.</p>
      </div>

      <RouterLink to="/tasks/create" class="create-button">
        <span>＋</span> New Task <span>↗</span>
      </RouterLink>
    </header>

    <div v-if="successMessage" class="notice success">
      {{ successMessage }}
    </div>

    <div v-if="errorMessage" class="notice error">
      {{ errorMessage }}
      <button @click="loadTasks">Try again</button>
    </div>

    <section class="glass-panel">
      <div class="panel-header">
        <div>
          <div class="eyebrow">YOUR ACTIVITIES</div>
          <h2>Task collection</h2>
          <p>{{ tasks.length }} activities in your workspace</p>
        </div>

        <button class="refresh-button" @click="loadTasks">
          ↻ Refresh
        </button>
      </div>

      <div class="toolbar">
        <div class="search-box">
          <span>⌕</span>
          <input
            v-model="search"
            type="search"
            placeholder="Search tasks..."
          />
        </div>

        <select v-model="statusFilter" class="filter-select">
          <option value="all">All Status</option>
          <option value="pending">To Do</option>
          <option value="in_progress">In Progress</option>
          <option value="completed">Completed</option>
        </select>
      </div>

      <div class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th>ACTIVITY</th>
              <th>DATE</th>
              <th>PRIORITY</th>
              <th>STATUS</th>
              <th>ACTIONS</th>
            </tr>
          </thead>

          <tbody>
            <tr v-if="loading">
              <td colspan="5" class="empty-state">
                Loading tasks from Laravel...
              </td>
            </tr>

            <tr v-else-if="filteredTasks.length === 0">
              <td colspan="5" class="empty-state">
                <div class="empty-icon">✦</div>
                <strong>No tasks found</strong>
                <p>Create your first task to get started.</p>
              </td>
            </tr>

            <tr
              v-for="task in loading ? [] : filteredTasks"
              :key="task.id"
            >
              <td>
                <div class="task-info">
                  <div class="task-icon">✦</div>
                  <div>
                    <strong>{{ task.title }}</strong>
                    <small>
                      {{ task.type }} ·
                      {{ task.description || 'No description' }}
                    </small>
                  </div>
                </div>
              </td>

              <td>{{ formatDate(task.activity_date) }}</td>

              <td>
                <span
                  class="pill"
                  :class="'priority-' + task.priority"
                >
                  {{ priorityLabel(task.priority) }}
                </span>
              </td>

              <td>
                <span
                  class="pill"
                  :class="'status-' + task.status"
                >
                  {{ statusLabel(task.status) }}
                </span>
              </td>

              <td>
                <div class="actions">
                  <RouterLink
                    :to="`/tasks/${task.id}`"
                    class="action-link"
                  >
                    View
                  </RouterLink>

                  <RouterLink
                    :to="`/tasks/${task.id}/edit`"
                    class="action-link"
                  >
                    Edit
                  </RouterLink>

                  <button
                    class="action-link delete"
                    :disabled="deletingId === task.id"
                    @click="handleDelete(task)"
                  >
                    {{ deletingId === task.id ? 'Deleting...' : 'Delete' }}
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="table-footer">
        <span>Showing {{ filteredTasks.length }} of {{ tasks.length }} tasks</span>
        <span>CONNECTED TO LARAVEL REST API</span>
      </div>
    </section>

    <footer class="page-footer">
      <span>SYAKIRATASK — YOUR DAY, BEAUTIFULLY ORGANIZED.</span>
      <span>© 2026</span>
    </footer>
  </div>
</template>

<style scoped>
* {
  box-sizing: border-box;
}

.tasks-page {
  min-height: 100vh;
  background:
    radial-gradient(circle at 90% 0%, #fff 0%, transparent 32%),
    #ededed;
  padding: 55px 6%;
  font-family: Inter, 'Segoe UI', sans-serif;
  color: #191919;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: end;
  gap: 20px;
  margin-bottom: 45px;
}

.eyebrow {
  font-size: 10px;
  letter-spacing: 2px;
  color: #888;
}

.page-header h1 {
  font-size: clamp(36px, 4vw, 54px);
  letter-spacing: -2px;
  margin: 12px 0;
}

.page-header h1 span {
  color: #aaa;
}

.page-header p,
.panel-header p {
  font-size: 13px;
  color: #888;
}

.create-button {
  display: flex;
  align-items: center;
  gap: 15px;
  text-decoration: none;
  background: #191919;
  color: #fff;
  padding: 17px 23px;
  border-radius: 12px;
  font-size: 13px;
  font-weight: 600;
  white-space: nowrap;
}

.create-button:hover {
  background: #383838;
}

.glass-panel {
  background: rgba(255, 255, 255, 0.75);
  border: 1px solid rgba(255, 255, 255, 0.95);
  border-radius: 24px;
  padding: 32px;
  backdrop-filter: blur(25px);
  box-shadow:
    0 20px 65px rgba(0, 0, 0, 0.06),
    inset 0 1px 1px #fff;
}

.panel-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
}

.panel-header h2 {
  margin: 10px 0;
  font-size: 28px;
  letter-spacing: -1px;
}

.refresh-button {
  border: 1px solid #ddd;
  background: #fff;
  padding: 11px 17px;
  border-radius: 10px;
  cursor: pointer;
}

.toolbar {
  display: flex;
  justify-content: space-between;
  gap: 15px;
  margin-bottom: 24px;
}

.search-box {
  display: flex;
  align-items: center;
  gap: 10px;
  width: 390px;
  max-width: 100%;
  background: #fff;
  border: 1px solid #ddd;
  border-radius: 12px;
  padding: 0 15px;
}

.search-box span {
  font-size: 24px;
  color: #999;
}

.search-box input {
  border: none;
  outline: none;
  height: 47px;
  width: 100%;
  font-size: 12px;
  background: transparent;
}

.filter-select {
  border: 1px solid #ddd;
  background: white;
  border-radius: 12px;
  padding: 0 16px;
  min-height: 47px;
}

.table-wrapper {
  width: 100%;
  overflow-x: auto;
}

table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
}

th {
  color: #999;
  font-size: 10px;
  font-weight: 600;
  letter-spacing: 1.2px;
  padding: 18px 12px;
  border-bottom: 1px solid #ddd;
}

td {
  padding: 22px 12px;
  font-size: 12px;
  color: #666;
  border-bottom: 1px solid #eaeaea;
}

.task-info {
  display: flex;
  align-items: center;
  gap: 13px;
  min-width: 220px;
}

.task-icon {
  width: 40px;
  height: 40px;
  border-radius: 11px;
  background: #eaeaea;
  display: grid;
  place-items: center;
  font-size: 17px;
  flex-shrink: 0;
}

.task-info strong {
  color: #222;
  display: block;
  margin-bottom: 7px;
  font-size: 13px;
}

.task-info small {
  color: #999;
  display: block;
  font-size: 11px;
  max-width: 230px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  text-transform: capitalize;
}

.pill {
  display: inline-block;
  font-size: 10px;
  padding: 8px 13px;
  border-radius: 30px;
  background: #eee;
  white-space: nowrap;
}

.priority-high {
  background: #222;
  color: white;
}

.priority-medium {
  background: #dedede;
  color: #444;
}

.priority-low {
  background: #f1f1f1;
  color: #888;
}

.status-in_progress {
  background: #222;
  color: white;
}

.status-completed {
  background: #dedede;
  color: #444;
}

.status-pending {
  background: #f3f3f3;
  color: #888;
}

.actions {
  display: flex;
  align-items: center;
  gap: 8px;
}

.action-link {
  padding: 9px 12px;
  border: 1px solid #ddd;
  background: white;
  border-radius: 9px;
  color: #444;
  font-size: 11px;
  text-decoration: none;
  cursor: pointer;
  font-family: inherit;
}

.action-link:hover {
  background: #eee;
}

.action-link.delete {
  color: #a44;
}

.action-link:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.empty-state {
  text-align: center;
  padding: 75px 20px;
  color: #999;
}

.empty-state strong {
  display: block;
  color: #333;
  font-size: 16px;
}

.empty-state p {
  font-size: 12px;
}

.empty-icon {
  font-size: 35px;
  margin-bottom: 15px;
}

.table-footer {
  padding-top: 25px;
  display: flex;
  justify-content: space-between;
  color: #999;
  font-size: 10px;
  letter-spacing: 0.5px;
}

.page-footer {
  display: flex;
  justify-content: space-between;
  margin-top: 35px;
  font-size: 9px;
  letter-spacing: 1px;
  color: #aaa;
}

.notice {
  padding: 14px 18px;
  border-radius: 11px;
  margin-bottom: 20px;
  font-size: 12px;
}

.notice.success {
  background: #e9f5ec;
  color: #226b39;
}

.notice.error {
  background: #fff0f0;
  color: #ad3d3d;
}

.notice button {
  margin-left: 10px;
  border: 0;
  background: transparent;
  text-decoration: underline;
  cursor: pointer;
}

@media (max-width: 800px) {
  .tasks-page {
    padding: 30px 20px;
  }

  .page-header {
    align-items: flex-start;
    flex-direction: column;
  }

  .glass-panel {
    padding: 20px;
  }

  .toolbar {
    flex-direction: column;
  }

  .search-box {
    width: 100%;
  }

  .table-footer {
    gap: 15px;
    flex-wrap: wrap;
  }
}
</style>
