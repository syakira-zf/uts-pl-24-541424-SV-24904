
<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api'

const router = useRouter()
const tasks = ref([])
const loading = ref(true)
const errorMessage = ref('')

const user = ref(
  JSON.parse(localStorage.getItem('syakiratask_user') || 'null'),
)

const totalTasks = computed(() => tasks.value.length)
const completedTasks = computed(
  () => tasks.value.filter((task) => task.status === 'completed').length,
)
const pendingTasks = computed(
  () => tasks.value.filter((task) => task.status === 'pending').length,
)
const inProgressTasks = computed(
  () => tasks.value.filter((task) => task.status === 'in_progress').length,
)

const upcomingTasks = computed(() =>
  [...tasks.value]
    .filter((task) => task.status !== 'completed')
    .filter((task) => task.activity_date >= new Date().toLocaleDateString('en-CA'))
    .sort((a, b) => a.activity_date.localeCompare(b.activity_date))
    .slice(0, 5),
)

async function loadTasks() {
  loading.value = true
  errorMessage.value = ''

  try {
    const response = await api.get('/tasks')
    tasks.value = response.data.data
  } catch (error) {
    errorMessage.value = 'Gagal mengambil data tugas dari server.'
    if (error.response?.status === 401) {
      router.push('/login')
    }
  } finally {
    loading.value = false
  }
}

async function logout() {
  try {
    await api.post('/logout')
  } catch (error) {
    console.error('Logout error:', error)
  } finally {
    localStorage.removeItem('syakiratask_token')
    localStorage.removeItem('syakiratask_user')
    router.push('/login')
  }
}

function formatDate(date) {
  if (!date) return '-'
  return new Date(`${date.slice(0, 10)}T00:00:00`).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  })
}

onMounted(loadTasks)
</script>

<template>
  <div class="app-layout">
    <aside class="sidebar">
      <div class="logo">
        <div class="logo-icon">S.</div>
        <div>
          <strong>SYAKIRATASK</strong>
          <small>PERSONAL WORKSPACE</small>
        </div>
      </div>

      <div class="nav-label">WORKSPACE</div>

      <nav class="navigation">
        <RouterLink to="/dashboard" class="nav-item active">
          <span>▦</span> Dashboard
        </RouterLink>
        <RouterLink to="/tasks" class="nav-item">
          <span>☷</span> My Tasks
        </RouterLink>
      </nav>

      <div class="sidebar-bottom">
        <div class="profile">
          <div class="avatar">
            {{ user?.name?.charAt(0)?.toUpperCase() || 'S' }}
          </div>
          <div class="profile-info">
            <strong>{{ user?.name || 'User' }}</strong>
            <small>Personal Account</small>
          </div>
        </div>

        <button class="logout-btn" @click="logout">
          Sign out <span>↗</span>
        </button>
      </div>
    </aside>

    <main class="main-content">
      <header class="topbar">
        <span class="breadcrumb">WORKSPACE / OVERVIEW</span>
        <span class="topbar-status">
          <span class="status-dot"></span>
          PERSONAL DASHBOARD
        </span>
      </header>

      <div class="dashboard-content">
        <div class="heading-row">
          <div>
            <div class="eyebrow">YOUR PERSONAL OVERVIEW</div>
            <h1>Good day, {{ user?.name?.split(' ')[0] || 'there' }}<span>.</span></h1>
            <p>Here's what's happening in your workspace today.</p>
          </div>

          <button class="primary-button" @click="router.push('/tasks')">
            Manage tasks <span>↗</span>
          </button>
        </div>

        <div v-if="errorMessage" class="error-box">
          {{ errorMessage }}
          <button @click="loadTasks">Try again</button>
        </div>

        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-top">
              <span>TOTAL TASKS</span>
              <span class="stat-symbol">↗</span>
            </div>
            <div class="stat-number">{{ loading ? '—' : totalTasks }}</div>
            <div class="stat-footer">All your activities</div>
          </div>

          <div class="stat-card">
            <div class="stat-top">
              <span>COMPLETED</span>
              <span class="stat-symbol">✓</span>
            </div>
            <div class="stat-number">{{ loading ? '—' : completedTasks }}</div>
            <div class="stat-footer">Successfully finished</div>
          </div>

          <div class="stat-card">
            <div class="stat-top">
              <span>IN PROGRESS</span>
              <span class="stat-symbol">◷</span>
            </div>
            <div class="stat-number">{{ loading ? '—' : inProgressTasks }}</div>
            <div class="stat-footer">Currently working on</div>
          </div>

          <div class="stat-card dark-card">
            <div class="stat-top">
              <span>TO DO</span>
              <span class="stat-symbol">✦</span>
            </div>
            <div class="stat-number">{{ loading ? '—' : pendingTasks }}</div>
            <div class="stat-footer">Waiting for your attention</div>
          </div>
        </div>

        <section class="glass-panel">
          <div class="panel-heading">
            <div>
              <div class="eyebrow">STAY ON TRACK</div>
              <h2>Upcoming agenda<span>.</span></h2>
              <p>Your next scheduled activities.</p>
            </div>

            <button class="text-button" @click="router.push('/tasks')">
              View all tasks ↗
            </button>
          </div>

          <div v-if="loading" class="empty-state">
            Loading your agenda...
          </div>

          <div v-else-if="upcomingTasks.length === 0" class="empty-state">
            <div class="empty-icon">✦</div>
            <h3>All clear.</h3>
            <p>No upcoming activities. Enjoy your free time.</p>
          </div>

          <div v-else class="task-list">
            <div v-for="task in upcomingTasks" :key="task.id" class="task-row">
              <div class="task-icon">✦</div>

              <div class="task-details">
                <strong>{{ task.title }}</strong>
                <small>{{ task.type }} · {{ formatDate(task.activity_date) }}</small>
              </div>

              <div class="task-priority" :class="task.priority">
                {{ task.priority }}
              </div>

              <div class="task-status">
                {{ task.status.replace('_', ' ') }}
              </div>
            </div>
          </div>
        </section>

        <div class="footer">
          <span>SYAKIRATASK — YOUR DAY, BEAUTIFULLY ORGANIZED.</span>
          <span>© 2026</span>
        </div>
      </div>
    </main>
  </div>
</template>

<style scoped>
* { box-sizing: border-box; }

.app-layout {
  display: flex;
  min-height: 100vh;
  background: #efefef;
  color: #191919;
  font-family: Inter, 'Segoe UI', sans-serif;
}

.sidebar {
  width: 260px;
  min-width: 260px;
  background: #121212;
  color: white;
  padding: 32px 22px;
  display: flex;
  flex-direction: column;
  position: sticky;
  top: 0;
  height: 100vh;
}

.logo {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 65px;
}

.logo-icon {
  border: 1px solid #ffffff70;
  border-radius: 11px;
  height: 40px;
  width: 40px;
  display: grid;
  place-items: center;
  font-family: Georgia, serif;
  font-size: 24px;
}

.logo strong {
  font-size: 12px;
  letter-spacing: 2px;
}

.logo small {
  display: block;
  margin-top: 6px;
  font-size: 8px;
  letter-spacing: 1px;
  color: #777;
}

.nav-label {
  font-size: 9px;
  letter-spacing: 2px;
  color: #777;
  padding: 0 12px;
  margin-bottom: 18px;
}

.navigation {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 14px;
  color: #999;
  padding: 15px;
  border-radius: 11px;
  font-size: 13px;
  text-decoration: none;
  transition: 0.2s;
}

.nav-item span {
  font-size: 20px;
}

.nav-item:hover,
.nav-item.active {
  background: #ffffff17;
  color: white;
}

.sidebar-bottom {
  margin-top: auto;
}

.profile {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 20px 0;
  border-top: 1px solid #ffffff20;
}

.avatar {
  width: 38px;
  height: 38px;
  background: #eee;
  color: #222;
  display: grid;
  place-items: center;
  border-radius: 10px;
  font-weight: 700;
}

.profile-info strong {
  font-size: 12px;
  display: block;
}

.profile-info small {
  color: #888;
  font-size: 10px;
  margin-top: 4px;
  display: block;
}

.logout-btn {
  background: none;
  color: #aaa;
  border: 0;
  cursor: pointer;
  width: 100%;
  text-align: left;
  padding: 15px 0;
  display: flex;
  justify-content: space-between;
}

.main-content {
  flex: 1;
  min-width: 0;
}

.topbar {
  height: 77px;
  padding: 0 48px;
  border-bottom: 1px solid #ddd;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.breadcrumb,
.topbar-status {
  font-size: 10px;
  letter-spacing: 1.5px;
  color: #888;
}

.topbar-status {
  display: flex;
  align-items: center;
  gap: 9px;
}

.status-dot {
  width: 7px;
  height: 7px;
  background: #242424;
  border-radius: 50%;
}

.dashboard-content {
  max-width: 1450px;
  margin: auto;
  padding: 55px 48px 30px;
}

.heading-row {
  display: flex;
  align-items: end;
  justify-content: space-between;
  gap: 20px;
  margin-bottom: 45px;
}

.eyebrow {
  color: #888;
  font-size: 10px;
  letter-spacing: 2px;
}

.heading-row h1 {
  font-size: clamp(30px, 3.5vw, 48px);
  letter-spacing: -2px;
  margin: 14px 0 10px;
  font-weight: 600;
}

.heading-row h1 span,
.panel-heading h2 span {
  color: #aaa;
}

.heading-row p,
.panel-heading p {
  color: #888;
  font-size: 13px;
}

.primary-button {
  border: 0;
  background: #161616;
  color: white;
  border-radius: 12px;
  padding: 16px 22px;
  cursor: pointer;
  display: flex;
  gap: 30px;
  font-weight: 600;
}

.primary-button:hover {
  background: #383838;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 18px;
  margin-bottom: 30px;
}

.stat-card {
  background: rgba(255, 255, 255, 0.75);
  backdrop-filter: blur(20px);
  border: 1px solid white;
  border-radius: 20px;
  padding: 25px;
  box-shadow: 0 12px 40px #00000007;
}

.stat-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  color: #888;
  font-size: 10px;
  letter-spacing: 1.2px;
}

.stat-symbol {
  font-size: 20px;
}

.stat-number {
  font-size: 55px;
  font-weight: 600;
  letter-spacing: -3px;
  margin-top: 17px;
}

.stat-footer {
  font-size: 11px;
  color: #999;
  margin-top: 8px;
}

.dark-card {
  background: #191919;
  color: white;
  border-color: #333;
}

.dark-card .stat-top,
.dark-card .stat-footer {
  color: #999;
}

.glass-panel {
  border: 1px solid white;
  border-radius: 23px;
  background: #ffffffa8;
  box-shadow: 0 15px 50px #00000008;
  padding: 35px;
}

.panel-heading {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
}

.panel-heading h2 {
  font-size: 28px;
  letter-spacing: -1px;
  margin: 10px 0;
}

.text-button {
  background: none;
  border: none;
  cursor: pointer;
  font-size: 12px;
  text-decoration: underline;
  text-underline-offset: 5px;
}

.task-row {
  border-top: 1px solid #e7e7e7;
  padding: 18px 0;
  display: flex;
  align-items: center;
  gap: 17px;
}

.task-icon {
  width: 39px;
  height: 39px;
  border-radius: 11px;
  background: #eeeeee;
  display: grid;
  place-items: center;
}

.task-details {
  flex: 1;
}

.task-details strong {
  display: block;
  font-size: 13px;
  margin-bottom: 6px;
}

.task-details small {
  color: #888;
  font-size: 11px;
}

.task-priority,
.task-status {
  text-transform: capitalize;
  font-size: 10px;
  padding: 8px 12px;
  border-radius: 30px;
  background: #eee;
  color: #555;
}

.task-priority.high {
  background: #222;
  color: white;
}

.task-priority.medium {
  background: #ddd;
}

.empty-state {
  text-align: center;
  padding: 75px 15px;
  color: #999;
  border-top: 1px solid #eee;
}

.empty-icon {
  font-size: 35px;
  margin-bottom: 15px;
}

.empty-state h3 {
  color: #222;
}

.error-box {
  padding: 15px;
  background: #fff1f1;
  margin-bottom: 20px;
  border-radius: 10px;
  color: #a33;
}

.error-box button {
  margin-left: 10px;
  cursor: pointer;
}

.footer {
  display: flex;
  justify-content: space-between;
  padding-top: 32px;
  color: #aaa;
  font-size: 9px;
  letter-spacing: 1px;
}

@media (max-width: 1100px) {
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 800px) {
  .app-layout {
    flex-direction: column;
  }

  .sidebar {
    width: 100%;
    min-width: 0;
    height: auto;
    position: relative;
    padding: 20px;
  }

  .logo {
    margin-bottom: 20px;
  }

  .navigation {
    flex-direction: row;
  }

  .sidebar-bottom {
    display: none;
  }

  .topbar {
    padding: 0 20px;
  }

  .dashboard-content {
    padding: 30px 20px;
  }

  .heading-row {
    align-items: start;
    flex-direction: column;
  }

  .stats-grid {
    grid-template-columns: 1fr 1fr;
  }
}

@media (max-width: 520px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }

  .task-priority,
  .task-status {
    display: none;
  }
}
</style>
