<template>
  <div class="home-page">
    <div class="hero">
      <div class="hero-icon">🏛️</div>
      <h1>UniRoom</h1>
      <p>ระบบจองห้องมหาวิทยาลัย ง่าย สะดวก รวดเร็ว</p>
      <router-link to="/login" class="btn-hero">เข้าสู่ระบบ</router-link>
    </div>
  </div>
</template>

<script>
export default {
  mounted() {
    if (localStorage.getItem('token')) this.$router.push('/dashboard')
  }
}
</script>

<style scoped>
.home-page {
  min-height: 100vh; display: flex; align-items: center; justify-content: center;
  background: linear-gradient(135deg, #667eea, #764ba2);
}
.hero { text-align: center; color: white; padding: 40px 20px; }
.hero-icon { font-size: 64px; margin-bottom: 16px; }
.hero h1   { font-size: 42px; font-weight: 800; margin-bottom: 12px; }
.hero p    { font-size: 17px; opacity: .85; margin-bottom: 28px; }
.btn-hero  {
  background: white; color: #667eea; font-weight: 700;
  padding: 12px 32px; border-radius: 10px; text-decoration: none;
  font-size: 16px; display: inline-block; transition: .2s;
}
.btn-hero:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,0,0,.2); }
</style>