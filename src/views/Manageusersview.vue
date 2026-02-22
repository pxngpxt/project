<template>
  <div>
    <div class="page-header" style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px">
      <div>
        <h1>👥 จัดการผู้ใช้</h1>
        <p>เพิ่ม ลบ ผู้ใช้งานในระบบ</p>
      </div>
      <button @click="openAdd" class="btn btn-primary">+ เพิ่มผู้ใช้</button>
    </div>

    <div v-if="alert.msg" :class="`alert alert-${alert.type}`">{{ alert.msg }}</div>

    <div class="card table-wrap">
      <div v-if="loading" class="loading-box">⏳ กำลังโหลด...</div>
      <table v-else>
        <thead>
          <tr>
            <th>ชื่อ-นามสกุล</th>
            <th>Username</th>
            <th>Email</th>
            <th>เบอร์โทร</th>
            <th>บทบาท</th>
            <th>วันที่สร้าง</th>
            <th>จัดการ</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="u in users" :key="u.user_id">
            <td>
              <div class="user-cell">
                <div class="mini-avatar">{{ u.first_name?.charAt(0) }}</div>
                {{ u.first_name }} {{ u.last_name }}
              </div>
            </td>
            <td><code>{{ u.username }}</code></td>
            <td>{{ u.email }}</td>
            <td>{{ u.phone || '-' }}</td>
            <td><span :class="roleBadge(u.role_id)">{{ u.role_name }}</span></td>
            <td>{{ formatDate(u.created_at) }}</td>
            <td>
              <button @click="deleteUser(u)" class="btn btn-danger btn-sm">🗑️ ลบ</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Add User Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="showModal=false">
      <div class="modal">
        <div class="modal-header">
          <h3>➕ เพิ่มผู้ใช้ใหม่</h3>
          <button class="modal-close" @click="showModal=false">×</button>
        </div>
        <div class="modal-body">
          <div class="form-row-2">
            <div class="form-group">
              <label class="form-label">ชื่อ *</label>
              <input v-model="form.first_name" class="form-control" placeholder="ชื่อ" />
            </div>
            <div class="form-group">
              <label class="form-label">นามสกุล *</label>
              <input v-model="form.last_name" class="form-control" placeholder="นามสกุล" />
            </div>
          </div>
          <div class="form-row-2">
            <div class="form-group">
              <label class="form-label">Username *</label>
              <input v-model="form.username" class="form-control" placeholder="username" />
            </div>
            <div class="form-group">
              <label class="form-label">รหัสผ่าน *</label>
              <input v-model="form.password" type="password" class="form-control" placeholder="password" />
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Email *</label>
            <input v-model="form.email" type="email" class="form-control" placeholder="email@example.com" />
          </div>
          <div class="form-row-2">
            <div class="form-group">
              <label class="form-label">เบอร์โทร</label>
              <input v-model="form.phone" class="form-control" placeholder="0812345678" />
            </div>
            <div class="form-group">
              <label class="form-label">บทบาท *</label>
              <select v-model="form.role_id" class="form-control">
                <option value="1">Admin</option>
                <option value="2">Staff</option>
                <option value="3">Student</option>
                <option value="4">Teacher</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button @click="showModal=false" class="btn btn-outline">ยกเลิก</button>
          <button @click="saveUser" :disabled="saving" class="btn btn-primary">
            <span v-if="saving" class="spinner"></span>
            <span v-else>เพิ่มผู้ใช้</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

const BASE = 'http://localhost/project/api_php/users.php'

export default {
  data() {
    return {
      users: [], loading: true, showModal: false, saving: false,
      form: { first_name: '', last_name: '', username: '', password: '', email: '', phone: '', role_id: '3' },
      alert: { msg: '', type: 'success' }
    }
  },
  mounted() { this.loadUsers() },
  methods: {
    async loadUsers() {
      this.loading = true
      const res = await axios.get(BASE)
      if (res.data.success) this.users = res.data.data
      this.loading = false
    },
    openAdd() { this.form = { first_name:'',last_name:'',username:'',password:'',email:'',phone:'',role_id:'3' }; this.showModal = true },
    async saveUser() {
      if (!this.form.first_name || !this.form.username || !this.form.password || !this.form.email) {
        this.alert = { msg: 'กรุณากรอกข้อมูลให้ครบ', type: 'danger' }
        return
      }
      this.saving = true
      const res = await axios.post(BASE, this.form)
      this.saving = false
      if (res.data.success) {
        this.alert = { msg: '✅ ' + res.data.message, type: 'success' }
        this.showModal = false; this.loadUsers()
      } else {
        this.alert = { msg: res.data.message, type: 'danger' }
      }
    },
    async deleteUser(u) {
      if (!confirm(`ยืนยันลบผู้ใช้ ${u.username}?`)) return
      const res = await axios.delete(BASE, { data: { user_id: u.user_id } })
      if (res.data.success) { this.alert = { msg: '✅ ลบสำเร็จ', type: 'success' }; this.loadUsers() }
    },
    formatDate(dt) { return new Date(dt).toLocaleDateString('th-TH') },
    roleBadge(r) { return { 1: 'badge badge-danger', 2: 'badge badge-info', 3: 'badge badge-success', 4: 'badge badge-warning' }[r] || 'badge' }
  }
}
</script>

<style scoped>
.form-row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.user-cell { display: flex; align-items: center; gap: 8px; }
.mini-avatar {
  width: 28px; height: 28px; border-radius: 50%; background: linear-gradient(135deg, #1a56db, #0ea5e9);
  color: white; font-weight: 700; font-size: 12px;
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
code { background: #f1f5f9; padding: 2px 6px; border-radius: 4px; font-size: 12px; }
.loading-box { padding: 40px; text-align: center; color: #94a3b8; }
</style>