<template>
  <div id="app" :class="{ 'sidebar-open': sidebarOpen }">

    <nav v-if="isLoggedIn" class="top-navbar">
      <div class="navbar-left">
        <button class="hamburger" @click="toggleSidebar">
          <span></span><span></span><span></span>
        </button>
        <div class="brand">
          <span class="brand-icon">🏛️</span>
          <span class="brand-text">UniRoom</span>
        </div>
      </div>
      <div class="navbar-right">
        <div class="nav-user">
          <div class="user-avatar">{{ userInitial }}</div>
          <div class="user-info">
            <span class="user-name">{{ userName }}</span>
            <span class="user-role">{{ userRole }}</span>
          </div>
        </div>
        <button class="btn-logout" @click="logout">ออกจากระบบ</button>
      </div>
    </nav>

    <aside v-if="isLoggedIn" class="sidebar" :class="{ open: sidebarOpen }">
      <nav class="sidebar-nav">
        <div class="nav-section-title">เมนูหลัก</div>
        <router-link to="/dashboard" class="nav-item" @click="closeSidebarMobile">📊 Dashboard</router-link>
        <router-link to="/rooms"     class="nav-item" @click="closeSidebarMobile">🏢 ห้องทั้งหมด</router-link>
        <router-link to="/booking"   class="nav-item" @click="closeSidebarMobile">📅 จองห้อง</router-link>
        <router-link to="/my-bookings" class="nav-item" @click="closeSidebarMobile">📋 การจองของฉัน</router-link>

        <template v-if="userRoleId <= 2">
          <div class="nav-section-title">จัดการระบบ</div>
          <router-link to="/manage-bookings" class="nav-item" @click="closeSidebarMobile">✅ อนุมัติการจอง</router-link>
          <router-link to="/manage-rooms"    class="nav-item" @click="closeSidebarMobile">⚙️ จัดการห้อง</router-link>
          <router-link v-if="userRoleId === 1" to="/manage-users" class="nav-item" @click="closeSidebarMobile">👥 จัดการผู้ใช้</router-link>
        </template>
      </nav>
    </aside>

    <div v-if="isLoggedIn && sidebarOpen" class="sidebar-overlay" @click="closeSidebar"></div>

    <main :class="isLoggedIn ? 'main-content' : 'full-content'">
      <router-view />
    </main>

  </div>
</template>

<script>
export default {
  data() {
    return {
      isLoggedIn: false,
      userName: '',
      userRole: '',
      userRoleId: 0,
      sidebarOpen: true
    }
  },

  computed: {
    userInitial() {
      return this.userName ? this.userName.charAt(0).toUpperCase() : 'U'
    }
  },

  mounted() {
    this.checkLogin()
    if (window.innerWidth < 992) this.sidebarOpen = false
  },

  methods: {
    checkLogin() {
      this.isLoggedIn = !!localStorage.getItem("token")
      if (this.isLoggedIn) {
        const user = JSON.parse(localStorage.getItem("user") || '{}')
        this.userName   = user.name || 'ผู้ใช้'
        this.userRoleId = user.role_id || 3
        const roleMap   = { 1: 'ผู้ดูแลระบบ', 2: 'เจ้าหน้าที่', 3: 'นักศึกษา', 4: 'อาจารย์' }
        this.userRole   = roleMap[user.role_id] || 'ผู้ใช้'
      }
    },
    logout() {
      localStorage.removeItem("token")
      localStorage.removeItem("user")
      this.isLoggedIn = false
      this.$router.push('/login')
    },
    toggleSidebar()     { this.sidebarOpen = !this.sidebarOpen },
    closeSidebar()      { this.sidebarOpen = false },
    closeSidebarMobile(){ if (window.innerWidth < 992) this.sidebarOpen = false }
  },

  watch: {
    '$route'() { this.checkLogin() }
  }
}
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap');

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
  --primary: #1a56db;
  --primary-dark: #1342a6;
  --primary-light: #e8f0fe;
  --success: #10b981;
  --danger: #ef4444;
  --warning: #f59e0b;
  --sidebar-w: 240px;
  --navbar-h: 60px;
  --bg: #f0f4ff;
  --border: #e2e8f0;
  --text: #1e293b;
  --text-muted: #64748b;
  --shadow: 0 1px 3px rgba(0,0,0,.08), 0 4px 12px rgba(0,0,0,.06);
  --radius: 12px;
  --radius-sm: 8px;
}

body {
  font-family: 'Sarabun', sans-serif;
  background: var(--bg);
  color: var(--text);
  font-size: 15px;
}

#app { min-height: 100vh; }

/* NAVBAR */
.top-navbar {
  position: fixed; top: 0; left: 0; right: 0; z-index: 1000;
  height: var(--navbar-h); background: white;
  border-bottom: 1px solid var(--border);
  display: flex; align-items: center; justify-content: space-between;
  padding: 0 20px; box-shadow: 0 2px 8px rgba(0,0,0,.06);
}
.navbar-left  { display: flex; align-items: center; gap: 16px; }
.navbar-right { display: flex; align-items: center; gap: 16px; }

.hamburger {
  background: none; border: none; cursor: pointer;
  display: flex; flex-direction: column; gap: 5px; padding: 4px;
}
.hamburger span {
  display: block; width: 22px; height: 2px;
  background: var(--text); border-radius: 2px;
}

.brand         { display: flex; align-items: center; gap: 8px; }
.brand-icon    { font-size: 24px; }
.brand-text    { font-size: 18px; font-weight: 700; color: var(--primary); }

.nav-user      { display: flex; align-items: center; gap: 10px; }
.user-avatar   {
  width: 36px; height: 36px; border-radius: 50%;
  background: linear-gradient(135deg, var(--primary), #0ea5e9);
  color: white; font-weight: 700; font-size: 14px;
  display: flex; align-items: center; justify-content: center;
}
.user-info     { display: flex; flex-direction: column; }
.user-name     { font-size: 13px; font-weight: 600; line-height: 1.2; }
.user-role     { font-size: 11px; color: var(--text-muted); }

.btn-logout {
  background: none; border: 1px solid var(--border);
  color: var(--text-muted); padding: 6px 14px;
  border-radius: 8px; cursor: pointer; font-size: 13px;
  font-family: inherit; transition: .2s;
}
.btn-logout:hover { background: #fef2f2; color: var(--danger); border-color: #fca5a5; }

/* SIDEBAR */
.sidebar {
  position: fixed; left: 0; top: var(--navbar-h);
  width: var(--sidebar-w); height: calc(100vh - var(--navbar-h));
  background: white; border-right: 1px solid var(--border);
  z-index: 900; overflow-y: auto;
  transition: transform .3s ease;
  transform: translateX(-100%);
}
.sidebar.open { transform: translateX(0); }

.sidebar-nav     { padding: 12px; }
.nav-section-title {
  font-size: 10px; font-weight: 700; text-transform: uppercase;
  letter-spacing: 1px; color: var(--text-muted); padding: 12px 10px 6px;
}
.nav-item {
  display: flex; align-items: center; gap: 10px;
  padding: 10px 12px; border-radius: var(--radius-sm);
  color: var(--text-muted); text-decoration: none;
  font-size: 14px; font-weight: 500; transition: .2s; margin-bottom: 2px;
}
.nav-item:hover              { background: var(--primary-light); color: var(--primary); }
.nav-item.router-link-active { background: var(--primary); color: white; }

/* OVERLAY */
.sidebar-overlay {
  position: fixed; inset: 0; background: rgba(0,0,0,.4);
  z-index: 850;
}

/* MAIN */
.main-content {
  margin-top: var(--navbar-h); padding: 24px;
  min-height: calc(100vh - var(--navbar-h));
  transition: margin-left .3s;
}
.sidebar-open .main-content { margin-left: var(--sidebar-w); }
.full-content { min-height: 100vh; }

/* CARDS */
.card {
  background: white; border-radius: var(--radius);
  box-shadow: var(--shadow); border: 1px solid var(--border);
}
.page-header { margin-bottom: 24px; }
.page-header h1 { font-size: 22px; font-weight: 700; }
.page-header p  { color: var(--text-muted); font-size: 14px; margin-top: 2px; }

/* BADGE */
.badge {
  display: inline-flex; align-items: center;
  padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: 600;
}
.badge-success   { background: #dcfce7; color: #166534; }
.badge-warning   { background: #fef9c3; color: #854d0e; }
.badge-danger    { background: #fee2e2; color: #991b1b; }
.badge-info      { background: #dbeafe; color: #1e40af; }
.badge-secondary { background: #f1f5f9; color: #475569; }

/* BUTTONS */
.btn {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 8px 18px; border-radius: var(--radius-sm);
  font-size: 14px; font-weight: 600; font-family: inherit;
  border: none; cursor: pointer; transition: .2s; text-decoration: none;
}
.btn-primary { background: var(--primary); color: white; }
.btn-primary:hover { background: var(--primary-dark); }
.btn-success { background: var(--success); color: white; }
.btn-danger  { background: var(--danger);  color: white; }
.btn-warning { background: var(--warning); color: white; }
.btn-outline {
  background: transparent; color: var(--primary);
  border: 1.5px solid var(--primary);
}
.btn-outline:hover { background: var(--primary-light); }
.btn-sm      { padding: 5px 12px; font-size: 13px; }
.btn:disabled { opacity: .6; cursor: not-allowed; }

/* FORMS */
.form-group   { margin-bottom: 16px; }
.form-label   { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; }
.form-control {
  width: 100%; padding: 9px 12px;
  border: 1.5px solid var(--border); border-radius: var(--radius-sm);
  font-size: 14px; font-family: inherit; background: white; outline: none; transition: .2s;
}
.form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(26,86,219,.1); }
select.form-control { cursor: pointer; }
textarea.form-control { resize: vertical; }

/* TABLE */
.table-wrap   { overflow-x: auto; }
table         { width: 100%; border-collapse: collapse; }
th {
  background: #f8fafc; padding: 11px 14px; text-align: left;
  font-size: 12px; font-weight: 700; text-transform: uppercase;
  letter-spacing: .5px; color: var(--text-muted);
  border-bottom: 1px solid var(--border);
}
td { padding: 13px 14px; font-size: 14px; border-bottom: 1px solid #f1f5f9; }
tr:hover td   { background: #f8fafc; }
tr:last-child td { border-bottom: none; }

/* ALERT */
.alert {
  padding: 12px 16px; border-radius: var(--radius-sm);
  font-size: 14px; margin-bottom: 16px;
}
.alert-success { background: #dcfce7; color: #166534; }
.alert-danger  { background: #fee2e2; color: #991b1b; }
.alert-warning { background: #fef9c3; color: #854d0e; }
.alert-info    { background: #dbeafe; color: #1e40af; }

/* MODAL */
.modal-overlay {
  position: fixed; inset: 0; background: rgba(0,0,0,.5);
  z-index: 2000; display: flex; align-items: center; justify-content: center; padding: 20px;
}
.modal {
  background: white; border-radius: var(--radius);
  width: 100%; max-width: 520px; max-height: 90vh;
  overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,.2);
  animation: modalIn .25s ease;
}
@keyframes modalIn {
  from { opacity: 0; transform: translateY(-20px) scale(.96); }
  to   { opacity: 1; transform: translateY(0) scale(1); }
}
.modal-header {
  padding: 20px 24px 16px; border-bottom: 1px solid var(--border);
  display: flex; align-items: center; justify-content: space-between;
}
.modal-header h3 { font-size: 17px; font-weight: 700; }
.modal-close {
  background: none; border: none; cursor: pointer; font-size: 22px;
  color: var(--text-muted); width: 32px; height: 32px;
  border-radius: 8px; display: flex; align-items: center; justify-content: center;
}
.modal-close:hover { background: #f1f5f9; }
.modal-body   { padding: 20px 24px; }
.modal-footer {
  padding: 16px 24px; border-top: 1px solid var(--border);
  display: flex; gap: 10px; justify-content: flex-end;
}

/* SPINNER */
.spinner {
  display: inline-block; width: 16px; height: 16px;
  border: 2px solid rgba(255,255,255,.3); border-top-color: white;
  border-radius: 50%; animation: spin .7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* RESPONSIVE */
@media (min-width: 992px) {
  .sidebar { transform: translateX(0); }
  .sidebar-open .main-content { margin-left: var(--sidebar-w); }
  .hamburger { display: none; }
  .sidebar-overlay { display: none !important; }
}
@media (max-width: 991px) {
  .user-info    { display: none; }
  .main-content { margin-left: 0 !important; }
}
@media (max-width: 576px) {
  .main-content { padding: 16px; }
}
</style>