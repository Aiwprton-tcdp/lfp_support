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
	background: #f9f9f9;
	padding: 15px;
	border-radius: 8px;
	min-width: 620px;
	max-width: 680px;
	position: absolute;
	top: 5vh;
	bottom: 4vh;
	left: 30vw;
	/* right: 10vw; */
	left: 50%;
	/* top: 34%; */
	transform: translate(-50%, 0%);
	max-height: 520px;
}
.supModal {
	max-height: 550px;
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
  <UsersForTransferWindow ref="UsersForTransferModal"></UsersForTransferWindow>
  <UsersForAddingWindow ref="UsersForAddingModal"></UsersForAddingWindow>

  <div v-if="show" class="modal-shadow" @click.self="closeModal">
    <div class="modal" :class="{ supModal: isSupporter }">
      <div class="close" @click="closeModal">&#10006;</div>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Тикет № {{ ticket.id }}</h2>

      <template v-if="ticket.active == 1">
        <div v-if="isSupporter == true && this.user_id == ticket.response_id">
          <button v-on:click="ShowUsersForTransferModal" class="float-center bg-transparent text-blue-600 hover:text-blue-900 font-semibold py-1 px-1">
            Передать
          </button>
          <span> | </span>
          <button v-on:click="ShowUsersForAddingModal" class="float-center bg-transparent text-blue-600 hover:text-blue-900 font-semibold py-1 px-1">
            Добавить ответственного
          </button>
        </div>
        <div v-else-if="isSupporter == false && this.user_id == ticket.user_id">
          <button v-on:click="CloseTicket" class="bg-transparent text-red-600 hover:text-red-900 font-semibold py-1 px-1">
            Завершить тикет
          </button>
        </div>
      </template>

      <div class="h-full pb-4">
        <ChatTemplate :ticket="ticket"></ChatTemplate>
      </div>
    </div>
  </div>
</template>

<script>
import { BX24 } from 'bx24';
import ChatTemplate from '@/Components/ChatTemplate.vue';
import UsersForTransferWindow from '@/Components/Modals/UsersForTransferWindow.vue';
import UsersForAddingWindow from '@/Components/Modals/UsersForAddingWindow.vue';

export default {
  name: "TicketModalWindow",
	components: {
    ChatTemplate,
    UsersForTransferWindow,
    UsersForAddingWindow,
  },
  data: function () {
    return {
      Parameters: new Object(),
      show: false,
      ticket: Object(),
      isSupporter: false,
      user_id: 0,
    }
  },
  mounted() {
		this.GetURLParameter();
    this.isSupporter = this.$page.props.isSupporter;
    this.user_id = this.$page.props.user_id;
  },
  methods: {
    closeModal: function() {
      this.show = false;
    },
    CloseTicket() {
      let ok = confirm("Вы уверены, что готовы завершить тикет? (Данное действие необратимо)");
      if (ok === false) {
        return;
      }

      const bx24 = new BX24(window, parent);

      bx24.getAuth().then(auth => {
        axios.post("/api/ticket/close", {
          token: auth,
          sid: this.Parameters,
          ticket_id: this.ticket.id
        }).then(response => {
          console.log(response);
					this.ticket.active = 0;
          this.show = false;
          
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
    ShowUsersForTransferModal: function() {
      this.$refs.UsersForTransferModal.show = true;
      this.$refs.UsersForTransferModal.ticket_id = this.ticket.id;
    },
    ShowUsersForAddingModal: function() {
      this.$refs.UsersForAddingModal.show = true;
      this.$refs.UsersForAddingModal.ticket_id = this.ticket.id;
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