
<script setup>
import { reactive, watch } from 'vue'

const props = defineProps({
  initialData: {
    type: Object,
    default: () => ({}),
  },
  saving: {
    type: Boolean,
    default: false,
  },
  submitLabel: {
    type: String,
    default: 'Save Task',
  },
})

const emit = defineEmits(['submit', 'cancel'])

function emptyTask() {
  const today = new Date()
  const localDate = [
    today.getFullYear(),
    String(today.getMonth() + 1).padStart(2, '0'),
    String(today.getDate()).padStart(2, '0'),
  ].join('-')

  return {
    title: '',
    description: '',
    type: 'task',
    activity_date: localDate,
    start_time: '',
    end_time: '',
    deadline: '',
    priority: 'medium',
    status: 'pending',
  }
}

const form = reactive(emptyTask())

function normalizeDateTime(value) {
  if (!value) return ''
  return String(value).replace(' ', 'T').slice(0, 16)
}

watch(
  () => props.initialData,
  (data) => {
    Object.assign(form, emptyTask(), {
      ...data,
      activity_date: data.activity_date
        ? String(data.activity_date).slice(0, 10)
        : emptyTask().activity_date,
      start_time: data.start_time
        ? String(data.start_time).slice(0, 5)
        : '',
      end_time: data.end_time
        ? String(data.end_time).slice(0, 5)
        : '',
      deadline: normalizeDateTime(data.deadline),
    })
  },
  { immediate: true },
)

function submitForm() {
  const payload = {
    title: form.title.trim(),
    description: form.description || null,
    type: form.type,
    activity_date: form.activity_date,
    start_time: form.start_time || null,
    end_time: form.end_time || null,
    deadline: form.deadline
      ? form.deadline.replace('T', ' ') + ':00'
      : null,
    priority: form.priority,
    status: form.status,
  }

  emit('submit', payload)
}
</script>

<template>
  <form @submit.prevent="submitForm">
    <h2 class="task-section-title">Activity information</h2>
    <p class="task-section-description">
      Fill in the details of your task or agenda.
    </p>

    <div class="task-form-grid">
      <div class="task-field full">
        <label for="task-title">TASK TITLE *</label>
        <input
          id="task-title"
          v-model="form.title"
          type="text"
          maxlength="255"
          placeholder="e.g. Submit final assignment"
          required
        />
      </div>

      <div class="task-field full">
        <label for="task-description">DESCRIPTION</label>
        <textarea
          id="task-description"
          v-model="form.description"
          placeholder="Add your activity notes..."
          rows="4"
        ></textarea>
      </div>

      <div class="task-field">
        <label for="task-type">ACTIVITY TYPE *</label>
        <select id="task-type" v-model="form.type" required>
          <option value="task">Task</option>
          <option value="meeting">Meeting</option>
          <option value="event">Event</option>
          <option value="study">Study</option>
          <option value="personal">Personal</option>
        </select>
      </div>

      <div class="task-field">
        <label for="activity-date">ACTIVITY DATE *</label>
        <input
          id="activity-date"
          v-model="form.activity_date"
          type="date"
          required
        />
      </div>

      <div class="task-field">
        <label for="start-time">START TIME</label>
        <input id="start-time" v-model="form.start_time" type="time" />
      </div>

      <div class="task-field">
        <label for="end-time">END TIME</label>
        <input id="end-time" v-model="form.end_time" type="time" />
      </div>

      <div class="task-field full">
        <label for="deadline">DEADLINE</label>
        <input
          id="deadline"
          v-model="form.deadline"
          type="datetime-local"
        />
      </div>

      <div class="task-field">
        <label for="priority">PRIORITY *</label>
        <select id="priority" v-model="form.priority" required>
          <option value="low">Low Priority</option>
          <option value="medium">Medium Priority</option>
          <option value="high">High Priority</option>
        </select>
      </div>

      <div class="task-field">
        <label for="status">STATUS *</label>
        <select id="status" v-model="form.status" required>
          <option value="pending">To Do</option>
          <option value="in_progress">In Progress</option>
          <option value="completed">Completed</option>
        </select>
      </div>
    </div>

    <div class="task-actions">
      <button
        type="button"
        class="task-btn task-btn-secondary"
        :disabled="saving"
        @click="emit('cancel')"
      >
        Cancel
      </button>

      <button
        type="submit"
        class="task-btn task-btn-primary"
        :disabled="saving"
      >
        {{ saving ? 'Saving...' : submitLabel }}
        <span>↗</span>
      </button>
    </div>
  </form>
</template>
