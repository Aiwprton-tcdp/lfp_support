<style scoped>
.modal-shadow {
	position: fixed;
	top: 0;
	left: 0;
	min-height: 100%;
	width: 100%;
	background: rgba(0, 0, 0, 0.39);
}
.modal {
	background: #fff;
	border-radius: 8px;
	padding: 15px;
	min-width: 620px;
	max-width: 680px;
	position: absolute;
	top: 50%;
	left: 50%;
	transform: translate(-50%, -50%);
}
.close {
	border-radius: 50%;
	color: #fff;
	display: flex;
	align-items: center;
	justify-content: center;
	position: absolute;
	top: 7px;
	right: 7px;
	width: 30px;
	height: 30px;
	cursor: pointer;
}
</style>

<template>
  <div v-if="show" class="modal-shadow" @click.self="closeModal">
    <div class="modal">
      <div class="close" @click="closeModal">&#10006;</div>

      <slot name="title">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Создание купона</h2>
      </slot>

      <slot name="body">
        <div class="grid mt-4 space-y-3">
          <label class="text-left text-gray-700">Укажите параметры для купона:</label>

					<div class="h-20 w-full grid grid-cols-2 space-x-3">
            <div class="col-span-1 flex-row m-auto space-x-3">
              <label class="align-middle">Дата истечения:</label>
              <input v-model="age" type="date" id="end_date" name="trip-end" min="2022-01-01" max="2023-01-01" class="align-middle">
            </div>

            <div class="col-span-1 flex-row m-auto space-x-3">
              <label class="align-middle">Ценность:</label>
              <select v-model="weight" id="WeightSelect" class="w-40 align-middle">
                <option v-for="w in weightes" :value="w" :key="w">{{ w }}</option>
              </select>
            </div>
					</div>
        </div>
      </slot>

      <slot name="footer">
        <div class="modal-footer">
          <button v-on:click="CreateNewCoupon" class="float-right mt-4 mx-1 bg-transparent hover:bg-green-500 text-green-700 font-semibold hover:text-white py-2 px-4 border border-green-500 hover:border-transparent rounded">
            Подтвердить
          </button>
        </div>
      </slot>
    </div>
  </div>
</template>

<script>
import { BX24 } from 'bx24';

export default {
  name: "NewCouponModalWindow",
	components: { },
  data() {
    return {
      show: false,
      errored: false,
			age: "",
      weight: 0,
      weightes: Array.from({length: 10}, (_, i) => i + 1),
    }
  },
  mounted() { },
  methods: {
		closeModal: function() {
      this.show = false;
    },
    CreateNewCoupon() {
      const bx24 = new BX24(window, parent);

			if (this.weight == 0 || this.age == "") {
				return this.$page.props.toast = {
					status: "error",
					message: "Указаны не все параметры"
				};
			} else if (new Date(this.age) <= new Date()) {
				return this.$page.props.toast = {
					status: "error",
					message: "Дата истечения купона должна быть позднее даты создания"
				};
			} else if (this.weight < 0 || this.weight > 10) {
				return this.$page.props.toast = {
					status: "error",
					message: "Указано значение ценности купона вне допустимого диапазона"
				};
			}

      bx24.getAuth().then(auth => {
        axios.post("/api/coupon/new", {
          token: auth,
          sid: this.$parent.Parameters,
          age: this.age,
          weight: this.weight,
        }).then(response => {
          this.show = false;
          let newCoupon = response.data.data;
          // newCoupon.number = this.GetCouponNumber(newCoupon.id);
          if (this.$parent.AllCoupons != undefined) {
            this.$parent.AllCoupons.push(newCoupon);
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
  }
}
</script>