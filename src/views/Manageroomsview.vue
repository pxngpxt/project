<template>
  <div>
    <div class="page-header" style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px">
      <div>
        <h1>🏢 จัดการห้อง</h1>
        <p>เพิ่ม แก้ไข ลบห้องในระบบ</p>
      </div>
      <button @click="openAdd" class="btn btn-primary">+ เพิ่มห้องใหม่</button>
    </div>

    <div v-if="alert.msg" :class="`alert alert-${alert.type}`">{{ alert.msg }}</div>

    <div class="card table-wrap">
      <div v-if="loading" class="loading-box">⏳ กำลังโหลด...</div>
      <table v-else>
        <thead>
          <tr>
            <th>รหัสห้อง</th>
            <th>ชื่อห้อง</th>
            <th>สถานที่</th>
            <th>ความจุ</th>
            <th>สถานะ</th>
            <th>จัดการ</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="r in rooms" :key="r.room_id">
            <td><strong>{{ r.room_code }}</strong></td>
            <td>{{ r.room_name }}</td>
            <td>{{ r.location }}</td>
            <td>{{ r.capacity }} คน</td>
            <td><span :class="statusClass(r.status)">{{ statusLabel(r.status) }}</span></td>
            <td>
              <div style="display:flex;gap:6px">
                <button @click="openEdit(r)" class="btn btn-outline btn-sm">✏️ แก้ไข</button>
                <button @click="deleteRoom(r)" class="btn btn-danger btn-sm">🗑️ ลบ</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal -->
    <teleport to="body">
      <div v-if="showModal" class="my-modal-overlay" @click.self="showModal=false">
        <div class="my-modal">
          <div class="my-modal-header">
            <h3>{{ editMode ? '✏️ แก้ไขห้อง' : '➕ เพิ่มห้องใหม่' }}</h3>
            <button class="my-modal-close" @click="showModal=false">×</button>
          </div>
          <div class="my-modal-body">
            <div class="form-row-2">
              <div class="form-group">
                <label class="form-label">รหัสห้อง *</label>
                <input v-model="form.room_code" class="form-control" placeholder="เช่น RM-601" />
              </div>
              <div class="form-group">
                <label class="form-label">ชื่อห้อง *</label>
                <input v-model="form.room_name" class="form-control" placeholder="เช่น ห้องประชุม C" />
              </div>
            </div>
            <div class="form-row-2">
              <div class="form-group">
                <label class="form-label">ความจุ (คน) *</label>
                <input v-model="form.capacity" type="number" class="form-control" placeholder="20" min="1" />
              </div>
              <div class="form-group">
                <label class="form-label">สถานะ</label>
                <select v-model="form.status" class="form-control">
                  <option value="Available">ว่าง</option>
                  <option value="Unavailable">ไม่ว่าง</option>
                  <option value="Maintenance">ซ่อมบำรุง</option>
                </select>
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">สถานที่ *</label>
              <input v-model="form.location" class="form-control" placeholder="เช่น อาคาร 1 ชั้น 2" />
            </div>
          </div>
          <div class="my-modal-footer">
            <button @click="showModal=false" class="btn btn-outline">ยกเลิก</button>
            <button @click="saveRoom" :disabled="saving" class="btn btn-primary">
              <span v-if="saving" class="spinner"></span>
              <span v-else>{{ editMode ? 'บันทึก' : 'เพิ่มห้อง' }}</span>
            </button>
          </div>
        </div>
      </div>
    </teleport>
  </div>
</template>

<script>
import axios from 'axios'

const BASE = 'http://localhost/project/api_php/rooms.php'
const empty = () => ({ room_code: '', room_name: '', capacity: '', location: '', status: 'Available' })

export default {
  data() {
    return {
      rooms: [], loading: true, showModal: false, editMode: false,
      form: empty(), saving: false,
      alert: { msg: '', type: 'success' }
    }
  },
  mounted() { this.loadRooms() },
  methods: {
    async loadRooms() {
      this.loading = true
      try {
        const res = await axios.get(BASE)
        if (res.data.success) this.rooms = res.data.data
      } catch {
        this.alert = { msg: 'โหลดข้อมูลไม่สำเร็จ', type: 'danger' }
      } finally {
        this.loading = false
      }
    },
    openAdd()   { this.form = empty(); this.editMode = false; this.showModal = true },
    openEdit(r) { this.form = { ...r }; this.editMode = true; this.showModal = true },

    async saveRoom() {
      this.alert = { msg: '', type: 'success' }
      if (!this.form.room_code || !this.form.room_name || !this.form.capacity || !this.form.location) {
        this.alert = { msg: 'กรุณากรอกข้อมูลให้ครบทุกช่อง', type: 'danger' }
        return
      }
      this.saving = true
      try {
        const res = await axios[this.editMode ? 'put' : 'post'](BASE, this.form)
        if (res.data.success) {
          this.alert = { msg: '✅ ' + res.data.message, type: 'success' }
          this.showModal = false
          this.loadRooms()
        } else {
          this.alert = { msg: res.data.message, type: 'danger' }
        }
      } catch {
        this.alert = { msg: 'เกิดข้อผิดพลาด กรุณาลองใหม่', type: 'danger' }
      } finally {
        this.saving = false
      }
    },

    async deleteRoom(r) {
      if (!confirm(`ยืนยันลบห้อง "${r.room_name}"?`)) return
      try {
        const res = await axios.delete(BASE, { data: { room_id: r.room_id } })
        if (res.data.success) {
          this.alert = { msg: '✅ ลบห้องสำเร็จ', type: 'success' }
          this.loadRooms()
        }
      } catch {
        this.alert = { msg: 'ลบไม่สำเร็จ', type: 'danger' }
      }
    },

    statusLabel(s) {
      return { Available: 'ว่าง', Unavailable: 'ไม่ว่าง', Maintenance: 'ซ่อมบำรุง' }[s] || s
    },
    statusClass(s) {
      return {
        Available:   'status-tag available',
        Unavailable: 'status-tag unavailable',
        Maintenance: 'status-tag maintenance'
      }[s] || 'status-tag'
    }
  }
}
</script>

<style scoped>
.form-row-2  { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.loading-box { padding: 40px; text-align: center; color: #94a3b8; }

.status-tag             { display: inline-block; padding: 5px 14px; border-radius: 20px; font-size: 13px; font-weight: 700; }
.status-tag.available   { background: #bbf7d0; color: #14532d; border: 1.5px solid #22c55e; }
.status-tag.unavailable { background: #fecaca; color: #7f1d1d; border: 1.5px solid #ef4444; }
.status-tag.maintenance { background: #fef08a; color: #713f12; border: 1.5px solid #eab308; }
</style>

<style>
/* ใช้ style ไม่มี scoped เพราะ teleport to body */
.my-modal-overlay {
  position: fixed; inset: 0; background: rgba(0,0,0,.55);
  z-index: 9999; display: flex; align-items: center; justify-content: center; padding: 20px;
}
.my-modal {
  background: white; border-radius: 14px;
  width: 100%; max-width: 540px; max-height: 90vh;
  overflow-y: auto; box-shadow: 0 25px 60px rgba(0,0,0,.3);
  animation: fadeIn .2s ease;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-16px) scale(.97); }
  to   { opacity: 1; transform: translateY(0) scale(1); }
}
.my-modal-header {
  padding: 20px 24px 16px; border-bottom: 1px solid #e2e8f0;
  display: flex; align-items: center; justify-content: space-between;
}
.my-modal-header h3  { font-size: 17px; font-weight: 700; margin: 0; color: #1e293b; }
.my-modal-close {
  background: none; border: none; cursor: pointer; font-size: 24px;
  color: #64748b; width: 34px; height: 34px; border-radius: 8px;
  display: flex; align-items: center; justify-content: center; line-height: 1;
}
.my-modal-close:hover { background: #f1f5f9; color: #ef4444; }
.my-modal-body   { padding: 20px 24px; }
.my-modal-footer {
  padding: 16px 24px; border-top: 1px solid #e2e8f0;
  display: flex; gap: 10px; justify-content: flex-end;
}
</style>