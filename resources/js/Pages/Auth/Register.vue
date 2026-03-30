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
          <h1 class="auth-title">Create account</h1>
          <p class="auth-subtitle">Start tracking your finances today</p>
        </div>

        <!-- Error Message -->
        <div v-if="errors && Object.keys(errors).length" class="alert-error">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          <span>{{ Object.values(errors)[0] }}</span>
        </div>

        <form @submit.prevent="submit" class="auth-form">
          <!-- Name -->
          <div class="form-group">
            <label>Full Name</label>
            <div class="input-icon">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
              <input
                v-model="form.name"
                type="text"
                class="form-input"
                placeholder="Alex Johnson"
                autocomplete="name"
                required
              />
            </div>
          </div>

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
            <label>Password</label>
            <div class="input-icon">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
              <input
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                class="form-input"
                placeholder="Min. 8 characters"
                autocomplete="new-password"
                required
              />
              <button type="button" class="eye-btn" @click="showPassword = !showPassword">
                <svg v-if="!showPassword" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
              </button>
            </div>
            <!-- Strength Bar -->
            <div class="strength-bar" v-if="form.password">
              <div class="strength-track">
                <div class="strength-fill" :style="`width:${pwdStrength.pct}%; background:${pwdStrength.color}`"></div>
              </div>
              <span class="strength-label" :style="`color:${pwdStrength.color}`">{{ pwdStrength.label }}</span>
            </div>
          </div>

          <!-- Confirm Password -->
          <div class="form-group">
            <label>Confirm Password</label>
            <div class="input-icon">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
              <input
                v-model="form.password_confirmation"
                :type="showConfirm ? 'text' : 'password'"
                class="form-input"
                placeholder="••••••••"
                autocomplete="new-password"
                required
              />
              <button type="button" class="eye-btn" @click="showConfirm = !showConfirm">
                <svg v-if="!showConfirm" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
              </button>
            </div>
            <span v-if="form.password_confirmation && form.password !== form.password_confirmation" class="form-error">Passwords do not match.</span>
          </div>

          <!-- Submit -->
          <button type="submit" class="btn-submit" :disabled="form.processing">
            <svg v-if="form.processing" class="spinner" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
            {{ form.processing ? 'Creating account...' : 'Create Account' }}
          </button>
        </form>

        <p class="auth-switch">
          Already have an account?
          <Link :href="route('login')" class="auth-link">Sign in</Link>
        </p>
      </div>
    </div>

    <!-- Right Panel -->
    <div class="auth-right">
      <div class="right-content">
        <div class="features-list">
          <div class="feature-item" v-for="(f, i) in features" :key="i" :style="`--delay: ${i * 0.12}s`">
            <div class="feature-icon" :style="`background: ${f.bg}`">
              <span v-html="f.icon"></span>
            </div>
            <div>
              <div class="feature-title">{{ f.title }}</div>
              <div class="feature-desc">{{ f.desc }}</div>
            </div>
          </div>
        </div>
        <h2 class="right-title">Everything you need to manage money smarter</h2>
        <p class="right-desc">Join thousands of users who trust FinanceApp to stay on top of their finances.</p>
      </div>
    </div>
  </div>
</template>

<script>
import { Link, useForm } from '@inertiajs/vue3'

export default {
  name: 'Register',
  components: { Link },
  props: {
    errors: Object,
  },
  data() {
    return {
      showPassword: false,
      showConfirm: false,
      form: useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
      }),
      features: [
        { title: 'Track Transactions',   desc: 'Log income and expenses easily.',           bg: '#10b98120', icon: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>' },
        { title: 'Budget Planning',      desc: 'Set limits and monitor spending.',           bg: '#3b82f620', icon: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>' },
        { title: 'Financial Reports',    desc: 'Visual analytics of your finances.',         bg: '#f59e0b20', icon: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>' },
        { title: 'Category Management',  desc: 'Organize transactions by category.',         bg: '#8b5cf620', icon: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#8b5cf6" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h7"/></svg>' },
      ],
    }
  },
  computed: {
    pwdStrength() {
      const p = this.form.password
      if (!p) return { pct: 0, label: '', color: '' }
      let s = 0
      if (p.length >= 8) s++
      if (/[A-Z]/.test(p)) s++
      if (/[0-9]/.test(p)) s++
      if (/[^A-Za-z0-9]/.test(p)) s++
      return [
        { pct: 25,  label: 'Weak',   color: '#ef4444' },
        { pct: 50,  label: 'Fair',   color: '#f59e0b' },
        { pct: 75,  label: 'Good',   color: '#3b82f6' },
        { pct: 100, label: 'Strong', color: '#10b981' },
      ][s - 1] || { pct: 25, label: 'Weak', color: '#ef4444' }
    }
  },
  methods: {
    submit() {
      this.form.post(route('register'), {
        onFinish: () => this.form.reset('password', 'password_confirmation'),
      })
    }
  }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=Syne:wght@700;800&display=swap');

* { box-sizing: border-box; margin: 0; padding: 0; }

.auth-shell { display: flex; min-height: 100vh; background: #080c14; font-family: 'DM Sans', sans-serif; color: #e2e8f0; }

.auth-left { width: 480px; flex-shrink: 0; display: flex; flex-direction: column; padding: 40px; background: #0d1220; border-right: 1px solid #ffffff0a; min-height: 100vh; }

.brand { display: flex; align-items: center; gap: 10px; margin-bottom: 40px; }

.logo-icon { width: 36px; height: 36px; background: #10b98118; border: 1px solid #10b98130; border-radius: 10px; display: flex; align-items: center; justify-content: center; }

.logo-text { font-family: 'Syne', sans-serif; font-size: 16px; font-weight: 700; color: #f1f5f9; }

.auth-card { flex: 1; display: flex; flex-direction: column; justify-content: center; max-width: 380px; width: 100%; margin: 0 auto; }

.auth-heading { margin-bottom: 28px; }

.auth-title { font-family: 'Syne', sans-serif; font-size: 28px; font-weight: 800; color: #f1f5f9; letter-spacing: -0.02em; margin-bottom: 8px; }

.auth-subtitle { font-size: 15px; color: #64748b; }

.alert-error { display: flex; align-items: center; gap: 8px; background: #ef444415; border: 1px solid #ef444430; border-radius: 10px; padding: 12px 16px; font-size: 14px; color: #ef4444; margin-bottom: 20px; }

.auth-form { display: flex; flex-direction: column; gap: 18px; }

.form-group { display: flex; flex-direction: column; gap: 8px; }

.form-group label { font-size: 13px; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; }

.input-icon { position: relative; display: flex; align-items: center; }

.input-icon > svg:first-child { position: absolute; left: 14px; color: #475569; pointer-events: none; z-index: 1; }

.form-input { width: 100%; background: #131b2e; border: 1px solid #ffffff0d; border-radius: 12px; padding: 13px 44px; font-size: 14px; color: #e2e8f0; font-family: 'DM Sans', sans-serif; outline: none; transition: border-color 0.2s, box-shadow 0.2s; }

.form-input:focus { border-color: #10b98150; box-shadow: 0 0 0 3px #10b98115; }

.form-input::placeholder { color: #334155; }

.eye-btn { position: absolute; right: 14px; background: none; border: none; color: #475569; cursor: pointer; display: flex; align-items: center; transition: color 0.2s; padding: 2px; }

.eye-btn:hover { color: #94a3b8; }

.strength-bar { display: flex; align-items: center; gap: 10px; margin-top: 2px; }

.strength-track { flex: 1; height: 4px; background: #1e293b; border-radius: 99px; overflow: hidden; }

.strength-fill { height: 100%; border-radius: 99px; transition: width 0.4s, background 0.4s; }

.strength-label { font-size: 12px; font-weight: 600; min-width: 48px; }

.form-error { font-size: 12px; color: #ef4444; }

.btn-submit { width: 100%; background: #10b981; border: none; border-radius: 12px; padding: 14px; font-size: 15px; font-weight: 600; color: #fff; font-family: 'DM Sans', sans-serif; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: background 0.2s, transform 0.15s; margin-top: 4px; }

.btn-submit:hover:not(:disabled) { background: #059669; transform: translateY(-1px); }

.btn-submit:disabled { opacity: 0.6; cursor: not-allowed; }

.spinner { animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

.auth-switch { text-align: center; font-size: 14px; color: #64748b; margin-top: 24px; }

.auth-link { color: #10b981; font-weight: 600; text-decoration: none; margin-left: 4px; transition: opacity 0.2s; }

.auth-link:hover { opacity: 0.7; }

/* Right Panel */
.auth-right { flex: 1; display: flex; align-items: center; justify-content: center; padding: 60px; background: radial-gradient(ellipse at 60% 40%, #10b98112 0%, transparent 60%), radial-gradient(ellipse at 20% 80%, #3b82f60a 0%, transparent 50%); }

.right-content { max-width: 400px; }

.features-list { display: flex; flex-direction: column; gap: 16px; margin-bottom: 40px; }

.feature-item { display: flex; align-items: center; gap: 16px; padding: 16px; background: #0d1220; border: 1px solid #ffffff0a; border-radius: 14px; animation: fadeUp 0.5s ease both; animation-delay: var(--delay); }

.feature-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }

.feature-title { font-size: 14px; font-weight: 600; color: #f1f5f9; margin-bottom: 3px; }

.feature-desc { font-size: 12px; color: #64748b; }

.right-title { font-family: 'Syne', sans-serif; font-size: 24px; font-weight: 800; color: #f1f5f9; letter-spacing: -0.02em; margin-bottom: 12px; line-height: 1.3; }

.right-desc { font-size: 15px; color: #64748b; line-height: 1.6; }

@keyframes fadeUp { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }

@media (max-width: 768px) {
  .auth-right { display: none; }
  .auth-left { width: 100%; border-right: none; }
}
</style>