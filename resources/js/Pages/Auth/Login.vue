<template>
  <div class="auth-shell">
    <!-- Left Panel -->
    <div class="auth-left">
      <div class="brand">
        <div class="logo-icon">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
            <circle cx="12" cy="12" r="10" stroke="#10b981" stroke-width="2"/>
            <path d="M12 6v6l4 2" stroke="#10b981" stroke-width="2" stroke-linecap="round"/>
          </svg>
        </div>
        <span class="logo-text">FinanceApp</span>
      </div>

      <div class="auth-card">
        <div class="auth-heading">
          <h1 class="auth-title">Welcome back</h1>
          <p class="auth-subtitle">Sign in to your account to continue</p>
        </div>

        <!-- Session Status -->
        <div v-if="status" class="alert-success">{{ status }}</div>

        <!-- Error Message -->
        <div v-if="errors && Object.keys(errors).length" class="alert-error">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          <span>{{ Object.values(errors)[0] }}</span>
        </div>

        <form @submit.prevent="submit" class="auth-form">
          <!-- Email -->
          <div class="form-group">
            <label>Email Address</label>
            <div class="input-icon">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
              <input
                v-model="form.email"
                type="email"
                class="form-input"
                placeholder="you@example.com"
                autocomplete="username"
                required
              />
            </div>
          </div>

          <!-- Password -->
          <div class="form-group">
            <div class="label-row">
              <label>Password</label>
              <Link v-if="canResetPassword" :href="route('password.request')" class="forgot-link">Forgot password?</Link>
            </div>
            <div class="input-icon">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
              <input
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                class="form-input"
                placeholder="••••••••"
                autocomplete="current-password"
                required
              />
              <button type="button" class="eye-btn" @click="showPassword = !showPassword">
                <svg v-if="!showPassword" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
              </button>
            </div>
          </div>

          <!-- Remember Me -->
          <div class="remember-row">
            <label class="checkbox-label">
              <input v-model="form.remember" type="checkbox" class="checkbox" />
              <span class="checkbox-custom"></span>
              <span>Remember me</span>
            </label>
          </div>

          <!-- Submit -->
          <button type="submit" class="btn-submit" :disabled="form.processing">
            <svg v-if="form.processing" class="spinner" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
            {{ form.processing ? 'Signing in...' : 'Sign In' }}
          </button>
        </form>

        <p class="auth-switch">
          Don't have an account?
          <Link :href="route('register')" class="auth-link">Create one</Link>
        </p>
      </div>
    </div>

    <!-- Right Panel (decorative) -->
    <div class="auth-right">
      <div class="right-content">
        <div class="stat-preview">
          <div class="preview-card" v-for="(card, i) in previewCards" :key="i" :style="`--delay: ${i * 0.15}s`">
            <span class="preview-label">{{ card.label }}</span>
            <span class="preview-value" :style="`color: ${card.color}`">{{ card.value }}</span>
          </div>
        </div>
        <h2 class="right-title">Take control of your finances</h2>
        <p class="right-desc">Track income, expenses, budgets, and reports — all in one place.</p>
      </div>
    </div>
  </div>
</template>

<script>
import { Link, useForm } from '@inertiajs/vue3'

export default {
  name: 'Login',
  components: { Link },
  props: {
    canResetPassword: Boolean,
    status: String,
    errors: Object,
  },
  data() {
    return {
      showPassword: false,
      form: useForm({
        email: '',
        password: '',
        remember: false,
      }),
      previewCards: [
        { label: 'Total Balance',   value: '$24,562',  color: '#10b981' },
        { label: 'Monthly Income',  value: '$8,450',   color: '#3b82f6' },
        { label: 'Monthly Expense', value: '$3,240',   color: '#ef4444' },
        { label: 'Savings',         value: '$5,210',   color: '#f59e0b' },
      ],
    }
  },
  methods: {
    submit() {
      this.form.post(route('login'), {
        onFinish: () => this.form.reset('password'),
      })
    }
  }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=Syne:wght@700;800&display=swap');

* { box-sizing: border-box; margin: 0; padding: 0; }

.auth-shell {
  display: flex;
  min-height: 100vh;
  background: #080c14;
  font-family: 'DM Sans', sans-serif;
  color: #e2e8f0;
}

/* ── Left Panel ── */
.auth-left {
  width: 480px;
  flex-shrink: 0;
  display: flex;
  flex-direction: column;
  padding: 40px;
  background: #0d1220;
  border-right: 1px solid #ffffff0a;
  min-height: 100vh;
}

.brand {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 48px;
}

.logo-icon {
  width: 36px; height: 36px;
  background: #10b98118;
  border: 1px solid #10b98130;
  border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
}

.logo-text {
  font-family: 'Syne', sans-serif;
  font-size: 16px; font-weight: 700;
  color: #f1f5f9;
}

.auth-card {
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: center;
  max-width: 380px;
  width: 100%;
  margin: 0 auto;
}

.auth-heading { margin-bottom: 32px; }

.auth-title {
  font-family: 'Syne', sans-serif;
  font-size: 28px; font-weight: 800;
  color: #f1f5f9;
  letter-spacing: -0.02em;
  margin-bottom: 8px;
}

.auth-subtitle { font-size: 15px; color: #64748b; }

/* Alerts */
.alert-success {
  background: #10b98118; border: 1px solid #10b98130;
  border-radius: 10px; padding: 12px 16px;
  font-size: 14px; color: #10b981; margin-bottom: 20px;
}

.alert-error {
  display: flex; align-items: center; gap: 8px;
  background: #ef444415; border: 1px solid #ef444430;
  border-radius: 10px; padding: 12px 16px;
  font-size: 14px; color: #ef4444; margin-bottom: 20px;
}

/* Form */
.auth-form { display: flex; flex-direction: column; gap: 20px; }

.form-group { display: flex; flex-direction: column; gap: 8px; }

.form-group label {
  font-size: 13px; font-weight: 600;
  color: #94a3b8;
  text-transform: uppercase; letter-spacing: 0.05em;
}

.label-row { display: flex; justify-content: space-between; align-items: center; }

.forgot-link {
  font-size: 13px; color: #10b981; font-weight: 500;
  text-decoration: none; transition: opacity 0.2s;
}

.forgot-link:hover { opacity: 0.7; }

.input-icon { position: relative; display: flex; align-items: center; }

.input-icon > svg:first-child {
  position: absolute; left: 14px;
  color: #475569; pointer-events: none; z-index: 1;
}

.form-input {
  width: 100%;
  background: #131b2e;
  border: 1px solid #ffffff0d;
  border-radius: 12px;
  padding: 13px 44px;
  font-size: 14px; color: #e2e8f0;
  font-family: 'DM Sans', sans-serif;
  outline: none;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.form-input:focus {
  border-color: #10b98150;
  box-shadow: 0 0 0 3px #10b98115;
}

.form-input::placeholder { color: #334155; }

.eye-btn {
  position: absolute; right: 14px;
  background: none; border: none;
  color: #475569; cursor: pointer;
  display: flex; align-items: center;
  transition: color 0.2s; padding: 2px;
}

.eye-btn:hover { color: #94a3b8; }

/* Remember */
.remember-row { margin-top: -4px; }

.checkbox-label {
  display: flex; align-items: center; gap: 10px;
  cursor: pointer; font-size: 14px; color: #64748b;
  user-select: none;
}

.checkbox { display: none; }

.checkbox-custom {
  width: 18px; height: 18px;
  border: 1.5px solid #334155;
  border-radius: 5px;
  background: #131b2e;
  display: flex; align-items: center; justify-content: center;
  transition: all 0.2s; flex-shrink: 0;
}

.checkbox:checked + .checkbox-custom {
  background: #10b981; border-color: #10b981;
}

.checkbox:checked + .checkbox-custom::after {
  content: '';
  width: 10px; height: 6px;
  border-left: 2px solid #fff;
  border-bottom: 2px solid #fff;
  transform: rotate(-45deg) translateY(-1px);
  display: block;
}

/* Submit */
.btn-submit {
  width: 100%;
  background: #10b981;
  border: none; border-radius: 12px;
  padding: 14px;
  font-size: 15px; font-weight: 600;
  color: #fff; font-family: 'DM Sans', sans-serif;
  cursor: pointer;
  display: flex; align-items: center; justify-content: center; gap: 8px;
  transition: background 0.2s, transform 0.15s;
  margin-top: 4px;
}

.btn-submit:hover:not(:disabled) { background: #059669; transform: translateY(-1px); }
.btn-submit:disabled { opacity: 0.6; cursor: not-allowed; }

.spinner { animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* Switch */
.auth-switch {
  text-align: center;
  font-size: 14px; color: #64748b;
  margin-top: 28px;
}

.auth-link {
  color: #10b981; font-weight: 600;
  text-decoration: none; margin-left: 4px;
  transition: opacity 0.2s;
}

.auth-link:hover { opacity: 0.7; }

/* ── Right Panel ── */
.auth-right {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 60px;
  background: radial-gradient(ellipse at 60% 40%, #10b98112 0%, transparent 60%),
              radial-gradient(ellipse at 20% 80%, #3b82f60a 0%, transparent 50%);
}

.right-content { max-width: 400px; }

.stat-preview {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 14px;
  margin-bottom: 40px;
}

.preview-card {
  background: #0d1220;
  border: 1px solid #ffffff0a;
  border-radius: 14px;
  padding: 18px;
  animation: fadeUp 0.5s ease both;
  animation-delay: var(--delay);
}

.preview-label { display: block; font-size: 11px; font-weight: 600; color: #475569; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 8px; }
.preview-value { display: block; font-family: 'Syne', sans-serif; font-size: 20px; font-weight: 800; letter-spacing: -0.02em; }

.right-title {
  font-family: 'Syne', sans-serif;
  font-size: 26px; font-weight: 800;
  color: #f1f5f9; letter-spacing: -0.02em;
  margin-bottom: 14px; line-height: 1.3;
}

.right-desc { font-size: 15px; color: #64748b; line-height: 1.6; }

@keyframes fadeUp { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }

@media (max-width: 768px) {
  .auth-right { display: none; }
  .auth-left { width: 100%; border-right: none; }
}
</style>