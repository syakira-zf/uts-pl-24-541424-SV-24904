
<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api'

const router = useRouter()

const email = ref('')
const password = ref('')
const showPassword = ref(false)
const loading = ref(false)
const errorMessage = ref('')

async function handleLogin() {
  errorMessage.value = ''
  loading.value = true

  try {
    const response = await api.post('/login', {
      email: email.value,
      password: password.value,
    })

    localStorage.setItem('syakiratask_token', response.data.token)
    localStorage.setItem('syakiratask_user', JSON.stringify(response.data.user))

    router.push('/dashboard')
  } catch (error) {
    if (error.response?.status === 401) {
      errorMessage.value = 'Email atau password tidak sesuai.'
    } else if (error.response?.status === 422) {
      errorMessage.value = 'Periksa kembali email dan password.'
    } else {
      errorMessage.value = 'Tidak dapat terhubung ke server. Coba lagi.'
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="login-page">
    <div class="ambient ambient-one"></div>
    <div class="ambient ambient-two"></div>

    <div class="login-layout">
      <section class="brand-section">
        <div class="brand-logo">
          <span class="logo-mark">S.</span>
          <span>SYAKIRATASK</span>
        </div>

        <div class="brand-content">
          <div class="eyebrow">
            <span class="eyebrow-line"></span>
            YOUR PERSONAL WORKSPACE
          </div>

          <h1>
            Make space<br />
            for what <em>matters.</em>
          </h1>

          <p>
            Semua tugas, agenda, dan prioritasmu dalam satu ruang
            yang tertata. Lebih fokus, lebih produktif.
          </p>

          <div class="brand-divider"></div>

          <div class="feature-row">
            <span>01 / PLAN</span>
            <span>02 / ORGANIZE</span>
            <span>03 / ACHIEVE</span>
          </div>
        </div>

        <div class="brand-footer">
          <span>PERSONAL TASK & AGENDA MANAGER</span>
          <span>© 2026 SYAKIRATASK</span>
        </div>
      </section>

      <section class="form-section">
        <div class="glass-card">
          <div class="card-top">
            <div class="card-icon">✦</div>
            <span class="card-label">WELCOME BACK</span>
          </div>

          <h2>Sign in<span class="heading-dot">.</span></h2>
          <p class="form-subtitle">
            Masuk untuk melanjutkan aktivitasmu.
          </p>

          <form @submit.prevent="handleLogin">
            <div class="field">
              <label for="email">EMAIL ADDRESS</label>
              <input
                id="email"
                v-model="email"
                type="email"
                placeholder="Enter your email"
                autocomplete="username"
                required
              />
            </div>

            <div class="field">
              <label for="password">PASSWORD</label>
              <div class="password-field">
                <input
                  id="password"
                  v-model="password"
                  :type="showPassword ? 'text' : 'password'"
                  placeholder="Enter your password"
                  autocomplete="current-password"
                  required
                />
                <button
                  class="toggle-password"
                  type="button"
                  @click="showPassword = !showPassword"
                >
                  {{ showPassword ? 'Hide' : 'Show' }}
                </button>
              </div>
            </div>

            <p v-if="errorMessage" class="error-message" role="alert">
              {{ errorMessage }}
            </p>

            <button class="login-button" type="submit" :disabled="loading">
              <span>{{ loading ? 'Signing in...' : 'Sign in to workspace' }}</span>
              <span class="button-arrow">↗</span>
            </button>
          </form>

          <div class="form-bottom">
            <span class="secure-icon">◇</span>
            SECURE ACCESS · POWERED BY LARAVEL SANCTUM
          </div>
        </div>

        <div class="right-footer">
          <span>DESIGNED FOR CLARITY.</span>
          <span>BUILT FOR PRODUCTIVITY.</span>
        </div>
      </section>
    </div>
  </div>
</template>

<style scoped>
* {
  box-sizing: border-box;
}

.login-page {
  min-height: 100vh;
  background: #e9e9e9;
  color: #171717;
  font-family: Inter, 'Segoe UI', sans-serif;
  position: relative;
  overflow: hidden;
}

.ambient {
  position: absolute;
  border-radius: 50%;
  filter: blur(80px);
  pointer-events: none;
}

.ambient-one {
  width: 450px;
  height: 450px;
  background: #fff;
  right: -100px;
  top: -150px;
}

.ambient-two {
  width: 350px;
  height: 350px;
  background: #bebebe;
  right: 20%;
  bottom: -200px;
  opacity: 0.35;
}

.login-layout {
  min-height: 100vh;
  display: grid;
  grid-template-columns: 1.05fr 1fr;
  position: relative;
  z-index: 1;
}

.brand-section {
  background: #111112;
  color: #fff;
  padding: 48px 65px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  position: relative;
  overflow: hidden;
}

.brand-section::before {
  content: '';
  position: absolute;
  width: 600px;
  height: 600px;
  border: 1px solid #ffffff15;
  border-radius: 50%;
  right: -350px;
  top: 15%;
  box-shadow:
    0 0 0 75px #ffffff04,
    0 0 0 150px #ffffff03,
    0 0 0 225px #ffffff02;
}

.brand-logo {
  display: flex;
  align-items: center;
  gap: 14px;
  font-size: 13px;
  font-weight: 700;
  letter-spacing: 3px;
  z-index: 1;
}

.logo-mark {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 44px;
  height: 44px;
  border: 1px solid #ffffff60;
  border-radius: 13px;
  font-size: 23px;
  letter-spacing: -2px;
}

.brand-content {
  position: relative;
  z-index: 1;
  max-width: 550px;
}

.eyebrow {
  font-size: 10px;
  letter-spacing: 3px;
  color: #a0a0a0;
  display: flex;
  align-items: center;
  gap: 14px;
  margin-bottom: 34px;
}

.eyebrow-line {
  width: 30px;
  height: 1px;
  background: #a0a0a0;
}

.brand-content h1 {
  font-size: clamp(48px, 5vw, 82px);
  font-weight: 500;
  letter-spacing: -4px;
  line-height: 1.06;
  margin: 0;
}

.brand-content h1 em {
  font-family: Georgia, serif;
  font-weight: 400;
  color: #b9b9b9;
}

.brand-content p {
  font-size: 15px;
  line-height: 1.9;
  color: #989898;
  margin-top: 30px;
  max-width: 380px;
}

.brand-divider {
  height: 1px;
  background: #ffffff25;
  margin: 55px 0 25px;
}

.feature-row {
  display: flex;
  justify-content: space-between;
  font-size: 10px;
  letter-spacing: 2px;
  color: #9c9c9c;
}

.brand-footer {
  display: flex;
  justify-content: space-between;
  font-size: 9px;
  letter-spacing: 1.5px;
  color: #777;
  position: relative;
  z-index: 1;
}

.form-section {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 70px 45px;
  position: relative;
}

.glass-card {
  width: 100%;
  max-width: 465px;
  padding: 47px;
  border-radius: 25px;
  background: rgba(255, 255, 255, 0.66);
  border: 1px solid rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(25px);
  -webkit-backdrop-filter: blur(25px);
  box-shadow:
    0 25px 80px rgba(0, 0, 0, 0.07),
    inset 0 1px 1px rgba(255, 255, 255, 0.95);
}

.card-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.card-icon {
  height: 45px;
  width: 45px;
  background: #171717;
  color: white;
  border-radius: 13px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
}

.card-label {
  color: #888;
  font-size: 10px;
  letter-spacing: 2px;
}

.glass-card h2 {
  margin: 38px 0 8px;
  font-size: 45px;
  font-weight: 600;
  letter-spacing: -2px;
}

.heading-dot {
  color: #999;
}

.form-subtitle {
  color: #888;
  font-size: 14px;
  margin-bottom: 40px;
}

.field {
  margin-bottom: 23px;
}

.field label {
  display: block;
  font-size: 10px;
  font-weight: 700;
  letter-spacing: 1.4px;
  margin-bottom: 11px;
}

.field input {
  height: 54px;
  width: 100%;
  background: #ffffffb5;
  border: 1px solid #dedede;
  border-radius: 12px;
  padding: 0 16px;
  outline: none;
  color: #171717;
  font-size: 13px;
  transition: 0.2s;
}

.field input:focus {
  border-color: #444;
  box-shadow: 0 0 0 3px #00000009;
}

.field input::placeholder {
  color: #aaa;
}

.password-field {
  position: relative;
}

.password-field input {
  padding-right: 78px;
}

.toggle-password {
  position: absolute;
  right: 14px;
  top: 50%;
  transform: translateY(-50%);
  border: none;
  background: transparent;
  color: #555;
  font-size: 12px;
  cursor: pointer;
}

.error-message {
  background: #fff1f1;
  border: 1px solid #ffd8d8;
  border-radius: 10px;
  padding: 12px;
  color: #9e3333;
  font-size: 12px;
}

.login-button {
  width: 100%;
  height: 55px;
  background: #181818;
  border: 1px solid #333;
  color: white;
  border-radius: 12px;
  padding: 0 20px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  cursor: pointer;
  font-size: 13px;
  font-weight: 600;
  margin-top: 32px;
  transition: 0.25s;
}

.login-button:hover {
  background: #383838;
  transform: translateY(-2px);
  box-shadow: 0 12px 25px #00000020;
}

.login-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.button-arrow {
  font-size: 22px;
}

.form-bottom {
  text-align: center;
  margin-top: 34px;
  color: #999;
  font-size: 9px;
  letter-spacing: 1.1px;
}

.secure-icon {
  font-size: 17px;
  vertical-align: middle;
  margin-right: 7px;
}

.right-footer {
  position: absolute;
  bottom: 27px;
  left: 45px;
  right: 45px;
  display: flex;
  justify-content: space-between;
  font-size: 9px;
  letter-spacing: 1.5px;
  color: #aaa;
}

@media (max-width: 900px) {
  .login-layout {
    grid-template-columns: 1fr;
  }

  .brand-section {
    min-height: 280px;
    padding: 30px;
  }

  .brand-content h1 {
    font-size: 42px;
    letter-spacing: -2px;
  }

  .brand-content p,
  .brand-divider,
  .feature-row,
  .brand-footer,
  .eyebrow {
    display: none;
  }

  .form-section {
    padding: 45px 20px 80px;
  }

  .glass-card {
    padding: 32px 24px;
  }

  .right-footer {
    left: 20px;
    right: 20px;
  }
}
</style>
