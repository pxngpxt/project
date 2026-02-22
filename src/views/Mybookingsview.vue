<template>
  <div>
    <div class="page-header">
      <h1>📋 การจองของฉัน</h1>
      <p>ดูและจัดการคำขอจองห้องของคุณ</p>
    </div>

    <!-- Filter -->
    <div class="card filter-bar">
      <select v-model="filterStatus" class="form-control" style="max-width:200px" @change="loadBookings">
        <option value="">ทุกสถานะ</option>
        <option value="Pending">รอดำเนินการ</option>
        <option value="Approved">อนุมัติแล้ว</option>
        <option value="Rejected">ปฏิเสธ</option>
        <option value="Cancelled">ยกเลิก</option>
      </select>
      <router-link to="/booking" class="btn btn-primary btn-sm">+ จองห้องใหม่</router-link>
    </div>

    <div v-if="loading" class="loading-box">⏳ กำลังโหลด...</div>
    <div v-else-if="bookings.length === 0" class="card empty-box">
      <span>📭</span>
      <p>ยังไม่มีประวัติการจอง</p>
      <router-link to="/booking" class="btn btn-primary btn-sm">จองห้องเลย</router-link>
    </div>

    <!-- Booking Cards -->
    <div v-else class="bookings-list">
      <div v-for="b in bookings" :key="b.booking_id" class="booking-card card">
        <div class="booking-card-header">
          <div>
            <span class="room-code-tag">{{ b.room_code }}</span>
            <h3>{{ b.room_name }}</h3>
          </div>
          <span :class="statusClass(b.status)">{{ statusLabel(b.status) }}</span>
        </div>
        <div class="booking-details">
          <div class="detail-item">
            <span>📍</span> {{ b.location }}
          </div>
          <div class="detail-item">
            <span>🕐</span> {{ formatDate(b.start_time) }} → {{ formatDate(b.end_time) }}
          </div>
          <div class="detail-item">
            <span>📝</span> {{ b.purpose }}
          </div>
          <div class="detail-item">
            <span>👥</span> ความจุ {{ b.capacity }} คน
          </div>
        </div>
        <div class="booking-card-footer">
          <span class="created-at">จองเมื่อ: {{ formatDate(b.created_at) }}</span>
          <button
            v-if="b.status === 'Pending'"
            @click="cancelBooking(b)"
            class="btn btn-danger btn-sm">
            ยกเลิก
          </button>
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
      bookings: [], loading: true, filterStatus: '',
      user: JSON.parse(localStorage.getItem('user') || '{}')
    }
  },
  mounted() { this.loadBookings() },
  methods: {
    async loadBookings() {
      this.loading = true
      try {
        const res = await axios.get('http://localhost/project/api_php/bookings.php', {
          params: { user_id: this.user.id, role_id: this.user.role_id, status: this.filterStatus }
        })
        if (res.data.success) this.bookings = res.data.data
      } catch { /* silent */ }
      finally { this.loading = false }
    },
    async cancelBooking(b) {
      if (!confirm('ยืนยันยกเลิกการจองนี้?')) return
      const res = await axios.put('http://localhost/project/api_php/bookings.php',
        { booking_id: b.booking_id, status: 'Cancelled' })
      if (res.data.success) this.loadBookings()
    },
    formatDate(dt) {
      return new Date(dt).toLocaleString('th-TH', {
        day: '2-digit', month: 'short', year: 'numeric',
        hour: '2-digit', minute: '2-digit'
      })
    },
    statusLabel(s) {
      return { Pending: '⏳ รอดำเนินการ', Approved: '✅ อนุมัติแล้ว', Rejected: '❌ ปฏิเสธ', Cancelled: '🚫 ยกเลิก' }[s] || s
    },
    statusClass(s) {
      return {
        Pending:   'badge badge-warning',
        Approved:  'badge badge-success',
        Rejected:  'badge badge-danger',
        Cancelled: 'badge badge-secondary'
      }[s]
    }
  }
}
</script>

<style scoped>
.filter-bar { padding: 12px 16px; display: flex; gap: 12px; align-items: center; margin-bottom: 20px; }

.bookings-list { display: flex; flex-direction: column; gap: 14px; }

.booking-card { overflow: hidden; transition: .2s; }
.booking-card:hover { box-shadow: 0 6px 20px rgba(0,0,0,.1); }

.booking-card-header {
  padding: 16px 20px; display: flex; align-items: flex-start;
  justify-content: space-between; border-bottom: 1px solid #f1f5f9;
}
.room-code-tag {
  font-size: 11px; font-weight: 700; color: #94a3b8;
  text-transform: uppercase; letter-spacing: .5px; display: block; margin-bottom: 2px;
}
.booking-card-header h3 { font-size: 16px; font-weight: 700; margin: 0; }

.booking-details { padding: 14px 20px; display: flex; flex-direction: column; gap: 8px; }
.detail-item { font-size: 13px; color: #475569; display: flex; align-items: flex-start; gap: 8px; }
.detail-item span:first-child { flex-shrink: 0; }

.booking-card-footer {
  padding: 12px 20px; background: #f8fafc;
  display: flex; align-items: center; justify-content: space-between;
}
.created-at { font-size: 12px; color: #94a3b8; }

.loading-box { text-align: center; padding: 40px; color: #94a3b8; }
.empty-box {
  padding: 60px; text-align: center; display: flex;
  flex-direction: column; align-items: center; gap: 12px;
}
.empty-box span { font-size: 40px; }
.empty-box p { color: #94a3b8; }
</style>