<style scoped>
.scrollbar-w-2::-webkit-scrollbar {
  width: 0.5rem;
  height: 0.25rem;
}
.scrollbar-track-blue-lighter::-webkit-scrollbar-track {
  --bg-opacity: 1;
  background-color: #f7fafc;
  background-color: rgba(247, 250, 252, var(--bg-opacity));
}
.scrollbar-thumb-blue::-webkit-scrollbar-thumb {
  --bg-opacity: 1;
  background-color: #edf2f7;
  background-color: rgba(237, 242, 247, var(--bg-opacity));
}
.scrollbar-thumb-rounded::-webkit-scrollbar-thumb {
  border-radius: 0.25rem;
}
</style>

<template>
  <div class="flex flex-col grid-cols-12">
    <table class="w-full">
      <thead class="">
        <tr class="border-b uppercase text-xs font-medium leading-4 tracking-wider text-center text-gray-500 bg-gray-100 grid grid-cols-12 gap-4">
          <th class="col-span-1 text-left	p-3">№</th>
          <th class="col-span-3	p-3">Ценность</th>
          <th class="col-span-4	p-3">{{ coupon_status == 1 ? "Дата создания" : "Дата завершения" }}</th>
          <th class="col-span-4	p-3">Действия</th>
        </tr>
      </thead>
    </table>
  </div>

  <div class="flex flex-col space-y-4 overflow-y-auto scrolling-touch"
    style="max-height:70vh">
    <table class="w-full grid-cols-12">
      <tbody class="bg-white">
        <tr v-for="(coupon, key) in this.$page.props.AllCoupons" :key="key" class=" mb-0.5 text-center grid grid-cols-12 gap-4" :class="ShowBorder(coupon)">
          <td class="col-span-1 text-left	p-3">{{ coupon.number }}</td>
          <td class="col-span-3	p-3">{{ coupon.weight }}</td>
          <td class="col-span-4	p-3">{{ coupon.updated_at }}</td>
          <td class="col-span-4	p-3">
            <span v-if="coupon.active == 0">Использован</span>
            <span v-else-if="coupon.active == 3">Просрочен</span>
            <button v-else-if="coupon_status == 1"
              v-on:click="CouponCancellation(coupon.id)"
              class="float-center bg-transparent text-red-600 hover:text-red-900 font-semibold">
              Аннулировать
            </button>
            <button v-else
              v-on:click="RepairCancellation(coupon.id)"
              class="float-center bg-transparent text-blue-600 hover:text-green-600 font-semibold">
              Восстановить
            </button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
import { BX24 } from 'bx24';

export default {
  components: { },
  props: ["coupon_status"],
  data() {
    return {
      Parameters: new Object(),
      loading: false,
      errored: false,
      coupons: new Array(),
      isSupporter: false,
    }
  },
  mounted() {
    this.GetURLParameter();
    if (this.$page.props.isSupporter === true) {
      this.GetCoupons();
    }
  },
  methods: {
    GetCoupons() {
      const bx24 = new BX24(window, parent);

      bx24.getAuth().then(auth => {
        axios.post("/api/coupons", {
          token: auth,
          sid: this.Parameters,
          coupon_status: this.coupon_status,
        }).then(response => {
          console.log(response.data.data);
          this.$page.props.AllCoupons = response.data.data;
					// this.coupons = response.data.data;
          // this.$page.props.AllCoupons.forEach(c => {
          //   c.number = this.GetCouponNumber(c.id);
          // });
        }).catch(error => {
          console.log(error);
          this.errored = true;
        });
      });
    },
    CouponCancellation(id) {
      const bx24 = new BX24(window, parent);

      bx24.getAuth().then(auth => {
        axios.post("/api/coupon/close", {
          token: auth,
          sid: this.Parameters,
          coupon_id: id,
        }).then(response => {
          if (response.data.status == "success") {
            this.$page.props.AllCoupons = this.$page.props.AllCoupons.filter(c => c.id !== id);
          }

          return this.$page.props.toast = {
            status: response.data.status,
            message: response.data.message
          }
        }).catch(error => {
          console.log(error);
          this.errored = true;
        });
      });
    },
    RepairCancellation(id) {
      const bx24 = new BX24(window, parent);

      bx24.getAuth().then(auth => {
        axios.post("/api/coupon/repair", {
          token: auth,
          sid: this.Parameters,
          coupon_id: id,
        }).then(response => {
          if (response.data.status == "success") {
            this.$page.props.AllCoupons = this.$page.props.AllCoupons.filter(c => c.id !== id);
          }

          return this.$page.props.toast = {
            status: response.data.status,
            message: response.data.message
          }
        }).catch(error => {
          console.log(error);
          this.errored = true;
        });
      });
    },
    GetCouponNumber(id) {
      let prepared = "00000000",
        length = id.toString().length,
        number = "#" + prepared.substring(0, 4-length) + id.toString();

      return number;
    },
    ShowBorder(coupon) {
      return coupon.active == 1 ? "border-b" : "border-0";
    },
    GetURLParameter() {
      let sPageURL = window.location.search.substring(1),
        sURLVariables = sPageURL.split('&');

      for (let i = 0; i < sURLVariables.length; i++) {
        let sParameterName = sURLVariables[i].split('=');
        this.Parameters[sParameterName[0]] = sParameterName[1];
      }
    }
  }
}
</script>