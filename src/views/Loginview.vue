<template>
  <div class="login-page">
    <!-- Background decoration -->
    <div class="bg-shapes">
      <div class="shape shape-1"></div>
      <div class="shape shape-2"></div>
      <div class="shape shape-3"></div>
    </div>

    <div class="login-container">
      <!-- Left panel -->
      <div class="login-left">
        <div class="left-content">
          <div class="logo-big">🏛️</div>
          <h1>UniRoom</h1>
          <p>ระบบจองห้องมหาวิทยาลัย<br>ง่าย สะดวก รวดเร็ว</p>
          <div class="features">
            <div class="feature-item">
              <span class="feature-icon">📅</span>
              <span>จองห้องได้ตลอด 24 ชั่วโมง</span>
            </div>
            <div class="feature-item">
              <span class="feature-icon">✅</span>
              <span>ตรวจสอบสถานะได้ทันที</span>
            </div>
            <div class="feature-item">
              <span class="feature-icon">🔔</span>
              <span>ระบบแจ้งเตือนผลการอนุมัติ</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Right panel - Login form -->
      <div class="login-right">
        <div class="login-card">
          <div class="login-card-header">
            <h2>เข้าสู่ระบบ</h2>
            <p>กรุณาใส่ข้อมูลเพื่อเข้าใช้งาน</p>
          </div>

          <!-- Alert -->
          <div v-if="error" class="alert alert-danger">{{ error }}</div>
          <div v-if="success" class="alert alert-success">{{ success }}</div>

          <div class="login-form">
            <!-- Username -->
            <div class="form-group">
              <label class="form-label">ชื่อผู้ใช้</label>
              <div class="input-icon">
                <svg class="icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                <input
                  v-model="username"
                  type="text"
                  class="form-control"
                  placeholder="กรอกชื่อผู้ใช้"
                  @keyup.enter="login"
                  :disabled="loading"
                />
              </div>
            </div>

            <!-- Password -->
            <div class="form-group">
              <label class="form-label">รหัสผ่าน</label>
              <div class="input-icon">
                <svg class="icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                <input
                  v-model="password"
                  :type="showPassword ? 'text' : 'password'"
                  class="form-control"
                  placeholder="กรอกรหัสผ่าน"
                  @keyup.enter="login"
                  :disabled="loading"
                />
                <button class="eye-btn" @click="showPassword = !showPassword" type="button">
                  <svg v-if="!showPassword" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                  <svg v-else width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                </button>
              </div>
            </div>

            <!-- Login Button -->
            <button class="btn-login" @click="login" :disabled="loading">
              <span v-if="loading" class="spinner"></span>
              <span v-else>เข้าสู่ระบบ</span>
            </button>
          </div>

          <!-- Demo accounts -->
          <div class="demo-accounts">
            <p class="demo-title">บัญชีทดสอบ:</p>
            <div class="demo-grid">
              <button v-for="acc in demoAccounts" :key="acc.username"
                class="demo-btn" @click="fillDemo(acc)">
                <span class="demo-role">{{ acc.label }}</span>
                <span class="demo-user">{{ acc.username }}</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  data() {
    return {
      username: '',
      password: '',
      error: '',
      success: '',
      loading: false,
      showPassword: false,
      demoAccounts: [
        { label: '🔑 Admin', username: 'admin01', password: 'admin1234' },
        { label: '👤 Staff', username: 'staff01', password: 'staff1234' },
        { label: '🎓 Student', username: 'student01', password: 'stu1234' },
        { label: '👨‍🏫 Teacher', username: 'teacher01', password: 'teach1234' }
      ]
    }
  },

  methods: {
    fillDemo(acc) {
      this.username = acc.username
      this.password = acc.password
    },

    async login() {
      this.error = ''
      if (!this.username || !this.password) {
        this.error = 'กรุณากรอกข้อมูลให้ครบ'
        return
      }
      try {
        this.loading = true
        const res = await axios.post(
          'http://localhost/project/api_php/login.php',
          { username: this.username, password: this.password }
        )
        if (res.data.success) {
          localStorage.setItem("token", "loggedIn")
          localStorage.setItem("user", JSON.stringify(res.data.user))
          this.$router.push('/dashboard')
        } else {
          this.error = res.data.message
        }
      } catch {
        this.error = 'ไม่สามารถเชื่อมต่อ Server ได้'
      } finally {
        this.loading = false
      }
    }
  }
}
</script>

<style scoped>
.login-page {
  min-height: 100vh; display: flex; align-items: center; justify-content: center;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 20px; position: relative; overflow: hidden;
}

.bg-shapes { position: absolute; inset: 0; pointer-events: none; }
.shape {
  position: absolute; border-radius: 50%;
  background: rgba(255,255,255,.05);
}
.shape-1 { width: 400px; height: 400px; top: -100px; right: -100px; }
.shape-2 { width: 250px; height: 250px; bottom: -60px; left: -60px; }
.shape-3 { width: 150px; height: 150px; top: 50%; left: 30%; }

.login-container {
  display: flex; width: 100%; max-width: 900px;
  background: white; border-radius: 20px;
  box-shadow: 0 25px 60px rgba(0,0,0,.25);
  overflow: hidden; position: relative; z-index: 1;
}

/* Left */
.login-left {
  flex: 1; background: linear-gradient(160deg, #1a56db 0%, #0ea5e9 100%);
  padding: 48px 40px; display: flex; align-items: center;
  color: white;
}
.left-content { width: 100%; }
.logo-big { font-size: 56px; margin-bottom: 16px; }
.login-left h1 { font-size: 32px; font-weight: 800; margin-bottom: 8px; }
.login-left p { font-size: 15px; opacity: .85; line-height: 1.7; margin-bottom: 32px; }

.features { display: flex; flex-direction: column; gap: 14px; }
.feature-item { display: flex; align-items: center; gap: 12px; font-size: 14px; }
.feature-icon { font-size: 20px; }

/* Right */
.login-right { flex: 1; padding: 48px 40px; }
.login-card-header { margin-bottom: 28px; }
.login-card-header h2 { font-size: 26px; font-weight: 800; color: #1e293b; }
.login-card-header p  { color: #64748b; margin-top: 4px; font-size: 14px; }

/* Input with icon */
.input-icon { position: relative; }
.input-icon .icon {
  position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
  stroke: #94a3b8; pointer-events: none;
}
.input-icon .form-control { padding-left: 38px; }
.eye-btn {
  position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
  background: none; border: none; cursor: pointer; color: #94a3b8;
  padding: 4px; display: flex; align-items: center;
}
.eye-btn:hover { color: #475569; }

.btn-login {
  width: 100%; padding: 12px; background: linear-gradient(135deg, #1a56db, #0ea5e9);
  color: white; border: none; border-radius: 10px;
  font-size: 15px; font-weight: 700; font-family: inherit;
  cursor: pointer; transition: .2s; margin-top: 8px;
  display: flex; align-items: center; justify-content: center; gap: 8px;
  box-shadow: 0 4px 14px rgba(26,86,219,.4);
}
.btn-login:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(26,86,219,.5); }
.btn-login:disabled { opacity: .7; transform: none; }

/* Demo accounts */
.demo-accounts { margin-top: 24px; padding-top: 20px; border-top: 1px solid #e2e8f0; }
.demo-title { font-size: 12px; color: #94a3b8; margin-bottom: 10px; font-weight: 600; }
.demo-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
.demo-btn {
  background: #f8fafc; border: 1.5px solid #e2e8f0;
  border-radius: 8px; padding: 8px 10px; cursor: pointer;
  text-align: left; transition: .2s; display: flex;
  flex-direction: column; gap: 2px;
}
.demo-btn:hover { background: #eff6ff; border-color: #93c5fd; }
.demo-role { font-size: 12px; font-weight: 600; color: #1e293b; }
.demo-user { font-size: 11px; color: #64748b; font-family: monospace; }

@media (max-width: 700px) {
  .login-left { display: none; }
  .login-right { padding: 32px 24px; }
}
</style>