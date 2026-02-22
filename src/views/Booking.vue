<template>
  <div>
    <div class="page-header">
      <h1>📅 จองห้อง</h1>
      <p>กรอกข้อมูลเพื่อส่งคำขอจองห้อง</p>
    </div>

    <div class="booking-layout">
      <!-- Form -->
      <div class="card booking-form-card">
        <div class="card-body">
          <div v-if="alert.msg" :class="`alert alert-${alert.type}`">{{ alert.msg }}</div>

          <div class="form-group">
            <label class="form-label">🏢 เลือกห้อง *</label>
            <select v-model="form.room_id" class="form-control" @change="onRoomChange">
              <option value="">-- เลือกห้อง --</option>
              <option v-for="r in availableRooms" :key="r.room_id" :value="r.room_id">
                {{ r.room_code }} - {{ r.room_name }} ({{ r.capacity }} คน) | {{ r.location }}
              </option>
            </select>
          </div>

          <div class="form-row-2">
            <div class="form-group">
              <label class="form-label">📅 วันและเวลาเริ่ม *</label>
              <input v-model="form.start_time" type="datetime-local" class="form-control" :min="minDate" />
            </div>
            <div class="form-group">
              <label class="form-label">🕐 วันและเวลาสิ้นสุด *</label>
              <input v-model="form.end_time" type="datetime-local" class="form-control" :min="form.start_time" />
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">📝 วัตถุประสงค์การใช้ห้อง *</label>
            <textarea v-model="form.purpose" class="form-control" rows="3"
              placeholder="เช่น ประชุมกลุ่ม, นำเสนองาน, สัมมนา..."></textarea>
          </div>

          <div v-if="duration" class="duration-info">
            ⏱️ ระยะเวลา: <strong>{{ duration }}</strong>
          </div>

          <button @click="submitBooking" :disabled="loading" class="btn btn-primary w-100">
            <span v-if="loading" class="spinner"></span>
            <span v-else>📤 ส่งคำขอจอง</span>
          </button>
        </div>
      </div>

      <!-- Right panel -->
      <div class="right-panel">
        <!-- Room Info -->
        <div v-if="selectedRoom" class="card room-info-card">
          <div class="room-info-header">
            <span class="big-emoji">🏫</span>
          </div>
          <div class="room-info-body">
            <div class="room-info-code">{{ selectedRoom.room_code }}</div>
            <h3>{{ selectedRoom.room_name }}</h3>
            <div class="info-rows">
              <div class="info-row">
                <span>📍 สถานที่</span>
                <strong>{{ selectedRoom.location }}</strong>
              </div>
              <div class="info-row">
                <span>👥 ความจุ</span>
                <strong>{{ selectedRoom.capacity }} คน</strong>
              </div>
              <div class="info-row">
                <span>🟢 สถานะ</span>
                <span class="badge badge-success">ว่างพร้อมใช้</span>
              </div>
            </div>
          </div>
        </div>

        <div v-else class="card room-placeholder">
          <span>🏢</span>
          <p>เลือกห้องเพื่อดูข้อมูล</p>
        </div>

        <!-- ตารางการจองของห้องนี้ -->
        <div v-if="selectedRoom" class="card schedule-card">
          <div class="schedule-header">
            <strong>📋 การจองห้องนี้</strong>
          </div>
          <div v-if="loadingSchedule" class="schedule-loading">⏳ กำลังโหลด...</div>
          <div v-else-if="roomBookings.length === 0" class="schedule-empty">
            ✅ ยังไม่มีการจองในห้องนี้
          </div>
          <div v-else class="schedule-list">
            <div v-for="b in roomBookings" :key="b.booking_id" class="schedule-item">
              <div class="schedule-user">
                <div class="mini-avatar">{{ b.first_name?.charAt(0) }}</div>
                <div>
                  <div class="schedule-name">{{ b.first_name }} {{ b.last_name }}</div>
                  <div class="schedule-purpose">{{ b.purpose }}</div>
                </div>
              </div>
              <div class="schedule-time">
                <div>▶ {{ formatDate(b.start_time) }}</div>
                <div>⏹ {{ formatDate(b.end_time) }}</div>
              </div>
              <span :class="statusClass(b.status)">{{ statusLabel(b.status) }}</span>
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
      rooms: [], loading: false, loadingSchedule: false,
      roomBookings: [],
      form: { room_id: '', start_time: '', end_time: '', purpose: '' },
      alert: { msg: '', type: 'success' },
      user: JSON.parse(localStorage.getItem('user') || '{}')
    }
  },

  computed: {
    availableRooms() { return this.rooms.filter(r => r.status === 'Available') },
    selectedRoom()   { return this.rooms.find(r => r.room_id == this.form.room_id) },
    minDate()        { return new Date().toISOString().slice(0, 16) },
    duration() {
      if (!this.form.start_time || !this.form.end_time) return ''
      const diff = new Date(this.form.end_time) - new Date(this.form.start_time)
      if (diff <= 0) return ''
      const h = Math.floor(diff / 3600000)
      const m = Math.floor((diff % 3600000) / 60000)
      return h > 0 ? `${h} ชั่วโมง ${m} นาที` : `${m} นาที`
    }
  },

  mounted() {
    this.loadRooms()
    if (this.$route.query.room_id) {
      this.form.room_id = this.$route.query.room_id
      this.$nextTick(() => this.loadRoomSchedule())
    }
  },

  methods: {
    async loadRooms() {
      const res = await axios.get('http://localhost/project/api_php/rooms.php')
      if (res.data.success) this.rooms = res.data.data
    },

    async onRoomChange() {
      this.roomBookings = []
      if (!this.form.room_id) return
      await this.loadRoomSchedule()
    },

    async loadRoomSchedule() {
      this.loadingSchedule = true
      try {
        const res = await axios.get('http://localhost/project/api_php/bookings.php', {
          params: { room_id: this.form.room_id, role_id: 1 }
        })
        if (res.data.success) {
          // แสดงเฉพาะที่ Pending และ Approved
          this.roomBookings = res.data.data.filter(b =>
            b.room_id == this.form.room_id &&
            ['Pending', 'Approved'].includes(b.status)
          )
        }
      } catch { /* silent */ }
      finally { this.loadingSchedule = false }
    },

    async submitBooking() {
      this.alert = { msg: '', type: 'success' }
      if (!this.form.room_id || !this.form.start_time || !this.form.end_time || !this.form.purpose) {
        this.alert = { msg: 'กรุณากรอกข้อมูลให้ครบ', type: 'danger' }
        return
      }
      if (new Date(this.form.end_time) <= new Date(this.form.start_time)) {
        this.alert = { msg: 'เวลาสิ้นสุดต้องมากกว่าเวลาเริ่มต้น', type: 'danger' }
        return
      }
      try {
        this.loading = true
        const res = await axios.post('http://localhost/project/api_php/bookings.php', {
          ...this.form, user_id: this.user.id
        })
        if (res.data.success) {
          this.alert = { msg: '✅ ' + res.data.message, type: 'success' }
          this.form = { room_id: '', start_time: '', end_time: '', purpose: '' }
          this.roomBookings = []
        } else {
          this.alert = { msg: res.data.message, type: 'danger' }
        }
      } catch {
        this.alert = { msg: 'เกิดข้อผิดพลาด', type: 'danger' }
      } finally { this.loading = false }
    },

    formatDate(dt) {
      return new Date(dt).toLocaleString('th-TH', {
        day: '2-digit', month: 'short', year: 'numeric',
        hour: '2-digit', minute: '2-digit'
      })
    },
    statusLabel(s) {
      return { Pending: 'รอดำเนินการ', Approved: 'อนุมัติแล้ว', Rejected: 'ปฏิเสธ', Cancelled: 'ยกเลิก' }[s] || s
    },
    statusClass(s) {
      return {
        Pending:   'status-tag pending',
        Approved:  'status-tag approved',
        Rejected:  'status-tag rejected',
        Cancelled: 'status-tag cancelled'
      }[s] || 'status-tag'
    }
  }
}
</script>

<style scoped>
.booking-layout {
  display: grid;
  grid-template-columns: 1fr 320px;
  gap: 20px; align-items: start;
}

.card-body    { padding: 24px; }
.form-row-2   { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
textarea.form-control { resize: vertical; }

.duration-info {
  background: #eff6ff; padding: 10px 14px; border-radius: 8px;
  font-size: 14px; color: #1d4ed8; margin-bottom: 16px;
}
.w-100 { width: 100%; justify-content: center; padding: 12px; font-size: 15px; }

/* Right panel */
.right-panel  { display: flex; flex-direction: column; gap: 16px; }

/* Room info */
.room-info-card { overflow: hidden; }
.room-info-header {
  height: 90px; background: linear-gradient(135deg, #dbeafe, #eff6ff);
  display: flex; align-items: center; justify-content: center;
}
.big-emoji { font-size: 44px; }
.room-info-body { padding: 14px 16px; }
.room-info-code { font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .5px; }
.room-info-body h3 { font-size: 15px; font-weight: 700; margin: 4px 0 12px; }
.info-rows  { display: flex; flex-direction: column; gap: 8px; }
.info-row   { display: flex; justify-content: space-between; align-items: center; font-size: 13px; color: #64748b; }

.room-placeholder {
  display: flex; flex-direction: column; align-items: center;
  justify-content: center; padding: 30px; text-align: center; color: #cbd5e1;
}
.room-placeholder span { font-size: 36px; margin-bottom: 8px; }
.room-placeholder p    { font-size: 13px; }

/* Schedule */
.schedule-card   { overflow: hidden; }
.schedule-header {
  padding: 12px 16px; border-bottom: 1px solid #f1f5f9;
  font-size: 14px; color: #1e293b; background: #f8fafc;
}
.schedule-loading,
.schedule-empty  { padding: 20px; text-align: center; font-size: 13px; color: #94a3b8; }
.schedule-list   { max-height: 340px; overflow-y: auto; }

.schedule-item {
  padding: 12px 16px; border-bottom: 1px solid #f1f5f9;
  display: flex; flex-direction: column; gap: 8px;
}
.schedule-item:last-child { border-bottom: none; }

.schedule-user  { display: flex; align-items: flex-start; gap: 8px; }
.mini-avatar {
  width: 28px; height: 28px; border-radius: 50%; flex-shrink: 0;
  background: linear-gradient(135deg, #1a56db, #0ea5e9);
  color: white; font-weight: 700; font-size: 12px;
  display: flex; align-items: center; justify-content: center;
}
.schedule-name    { font-size: 13px; font-weight: 600; color: #1e293b; }
.schedule-purpose { font-size: 12px; color: #64748b; margin-top: 1px; }

.schedule-time { font-size: 12px; color: #475569; display: flex; flex-direction: column; gap: 2px; padding-left: 36px; }

/* Status tags */
.status-tag             { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; margin-left: 36px; }
.status-tag.pending     { background: #fef08a; color: #713f12; border: 1.5px solid #eab308; }
.status-tag.approved    { background: #bbf7d0; color: #14532d; border: 1.5px solid #22c55e; }
.status-tag.rejected    { background: #fecaca; color: #7f1d1d; border: 1.5px solid #ef4444; }
.status-tag.cancelled   { background: #e2e8f0; color: #334155; border: 1.5px solid #94a3b8; }

@media (max-width: 768px) {
  .booking-layout { grid-template-columns: 1fr; }
  .form-row-2     { grid-template-columns: 1fr; }
}
</style>