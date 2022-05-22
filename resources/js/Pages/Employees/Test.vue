<template>
<div class="flex items-center justify-center space-x-2 animate-bounce" v-if="loading">
	<div class="flex absolute">
		<div class="w-8 h-8 bg-red-500 rounded-full"></div>
		<div class="w-8 h-8 bg-blue-700 rounded-full"></div>
		<div class="w-8 h-8 bg-blue-100 rounded-full"></div>
	</div>
</div>
<div v-else>
	<div class="flex flex-col bg-gray-50">
    <!-- <Table :specialty="this.$page.props.specialty" :sales_department="this.$page.props.sales_department" :city="this.$page.props.city"></Table> -->
	</div>
</div>
<Notification :toast="this.$page.props.toast" />
</template>

<script>
import { Head, Link } from '@inertiajs/inertia-vue3'
// import Query from '@/Components/Other/Query.vue'
// import Table from '@/Components/Other/Table.vue'
import Notification from '@/Components/Notification.vue'

export default {
  components: {
      Head,
      Link,
      // Query,
      // Table,
      Notification
    },
    props: {
      data: new Object()
    },
    data() {
      return {
        contacts: [],
        loading: false
      }
    },
    mounted() {
      this.$page.props.sales_department = this.data.salesDepartment;
      this.$page.props.specialty = this.data.specialty;
      this.$page.props.statuses = this.data.statuses;
      this.$page.props.city = this.data.city;
    }
  }
</script>