<style scoped>
.modal-shadow {
	position: fixed;
	top: 0;
	left: 0;
	min-height: 100%;
	width: 100%;
	background: rgba(0, 0, 0, 0.39);
	z-index: 1000 !important;
}
.modal {
	background: #f9f9f9;
	padding: 15px;
	border-radius: 8px;
	min-width: 620px;
	max-width: 680px;
	position: absolute;
	left: 50%;
	top: 50%;
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
.form-select {
	background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23a0aec0'%3e%3cpath d='M15.3 9.3a1 1 0 0 1 1.4 1.4l-4 4a1 1 0 0 1-1.4 0l-4-4a1 1 0 0 1 1.4-1.4l3.3 3.29 3.3-3.3z'/%3e%3c/svg%3e");
	-webkit-appearance: none;
	-moz-appearance: none;
	appearance: none;
	background-repeat: no-repeat;
	background-color: #fff;
	border-color: #e2e8f0;
	border-width: 1px;
	border-radius: 0.25rem;
	padding-top: 0.5rem;
	padding-right: 2.5rem;
	padding-bottom: 0.5rem;
	padding-left: 0.75rem;
	font-size: 1rem;
	line-height: 1.5;
	background-position: right 0.5rem center;
	background-size: 1.5em 1.5em;
}
.form-select::-ms-expand {
	color: #a0aec0;
	border: none;
}
@media not print {
	.form-select::-ms-expand {
		display: none;
	}
}
@media print and (-ms-high-contrast: active),
print and (-ms-high-contrast: none) {
	.form-select {
		padding-right: 0.75rem;
	}
}
.form-select:focus {
	outline: none;
	box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.5);
	border-color: #63b3ed;
}
</style>

<template>
  <div v-if="show" class="modal-shadow" @click.self="closeModal">
    <div class="modal">
      <div class="close" @click="closeModal">&#10006;</div>

      <slot name="title">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Выбор сотрудника для добавления ответственного к тикету</h2>
      </slot>

      <slot name="body">
				<select v-model="user_id" id="ReasonSelect" class="form-select block w-full my-4">
					<option v-for="u in users" :value="u.id" :key="u.id">
						{{ u.last_name }} {{ u.name }} {{ u.patronymic }}
					</option>
				</select>
      </slot>

      <slot name="footer">
        <div class="modal-footer">
					<button v-on:click="AddingResponsiveToTicket"
						class="hover:bg-green-500 text-green-700 font-semibold hover:text-white py-2 px-4 border border-green-500 rounded">
						Добавить
					</button>
        </div>
      </slot>
    </div>
  </div>
</template>

<script>
import { BX24 } from 'bx24';

export default {
  name: "UsersForAddingWindow",
	components: { },
  data: function () {
    return {
      show: false,
			users: new Array(),
			ticket_id: 0,
			user_id: 0,
    }
  },
  mounted() {
    if (this.$page.props.isSupporter === true) {
      this.GetUsers();
    }
  },
  methods: {
    closeModal: function() {
      this.show = false;
    },
		GetUsers: function() {
      const bx24 = new BX24(window, parent);

      bx24.getAuth().then(auth => {
        axios.post("/api/responsives/get", {
          token: auth,
          sid: this.$parent.Parameters,
        }).then(response => {
					this.users = response.data.responsives;
        }).catch(error => {
          console.log(error);
          this.errored = true;
        });
			});
		},
		AddingResponsiveToTicket() {
			if (this.user_id == 0) {
				return this.$page.props.toast = {
					status: "error",
					message: "Необходимо выбрать сотрудника для добавления"
				}
			} else if (this.ticket_id == 0) {
				return this.$page.props.toast = {
					status: "error",
					message: "Непредвиденная ошибка. Попробуйте перезагрузить страницу"
				}
			}
			
      const bx24 = new BX24(window, parent);

      bx24.getAuth().then(auth => {
        axios.post("/api/ticket/add_responsive", {
          token: auth,
          sid: this.$parent.Parameters,
          ticket_id: this.ticket_id,
					response_id: this.user_id,
        }).then(response => {
					if (response.data.status == "success") {
          	this.show = false;
					}
					// this.$parent.show = false;
					
					// if (response.data.status == "error") {
					// 	this.$page.props.AllTickets.forEach(t => {
					// 		if (t.id == this.ticket_id) {
					// 			t.active = 0;
					// 		}
					// 	});
					// }
          return this.$page.props.toast = {
            status: response.data.status,
            message: response.data.message
          };
        }).catch(error => {
          console.log(error);
          this.errored = true;
        });
      });
		},
  }
}
</script>