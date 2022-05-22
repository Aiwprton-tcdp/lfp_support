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
  <TicketModalWindow ref="CurrentTicketModal"></TicketModalWindow>

  <div class="flex flex-col grid-cols-12">
    <table class="w-full">
      <thead class="">
        <tr class="border-b uppercase text-xs font-medium leading-4 tracking-wider text-center text-gray-500 bg-gray-100 grid gap-4" :class="GetCols()">
          <th class="col-span-1 text-left p-3">№</th>
          <th class="col-span-4	p-3">Название</th>
          <th v-if="ticket_status == 1" class="col-span-3 p-3">Статус</th>
          <th v-if="isSupporter" class="col-span-1 text-left p-3">Вес</th>
          <th v-else-if="ticket_status == 1" class="col-span-1 p-3">Очередь</th>
          <th class="col-span-3	p-3">{{ ticket_status == 1 ? "Последнее изменение" : "Дата завершения" }}</th>
        </tr>
      </thead>
    </table>
  </div>

  <div class="flex flex-col space-y-4 overflow-y-auto scrolling-touch"
    style="max-height:70vh">
    <table class="w-full">
      <tbody class="bg-white">
        <tr v-for="(ticket, id) in this.$page.props.AllTickets" :key="id" class="mb-0.5 text-center grid gap-4" :class="ShowBorder(ticket)">
          <template v-if="ticket.active == ticket_status">
            <td class="flex col-span-1 text-left p-3">
              {{ ticket.id }}
              <span v-if="user_id != ticket.user_id && isSupporter == false"
                title="Этот тикет был создан одним из Ваших сотрудников, Вы за него не ответственный"
                class="cursor-help">+</span>
              <span v-else-if="user_id == ticket.user_id && isSupporter == true"
                title="Этот тикет был создан Вами, Вы за него не ответственный"
                class="cursor-help">++</span>
              <span v-else-if="user_id != ticket.user_id && user_id != ticket.response_id && isSupporter == true"
                title="Вы были добавлены отвественным к этому тикету"
                class="cursor-help">+</span>
            </td>

            <td class="col-span-4	p-3">
              <button v-on:click="ShowCurrentTicketModal(ticket)"
                class="float-center bg-transparent text-blue-600 hover:text-green-600 font-semibold">
                {{ ticket.name }}
              </button>
            </td>

            <td v-if="ticket_status == 1" class="col-span-3 p-3">
              <span v-if="ticket.status == true">Ожидайте ответа</span>
              <span v-else class="text-green-400">Новое сообщение</span>
            </td>

            <td v-if="isSupporter" class="col-span-1 flex flex-row text-left space-x-2 items-center p-3">
              <span>{{ ticket.weight }}</span>
              
              <img v-if="isSupporter && ticket.with_coupon"
                src='https://www.pngrepo.com/png/301779/180/coupon.png'
                alt='coupon'
                title="К этому тикету был применён купон"
                height='15'
                width='25' />
            </td>
            <td v-else-if="ticket_status == 1" class="col-span-1 p-3">{{ ticket.queue_number }}</td>

            <td class="col-span-3	p-3">{{ FormatDate(ticket.updated_at) }}</td>
          </template>
        </tr>
      </tbody>
    </table>
  </div>
</template>


<script>
import { BX24 } from 'bx24';
import TicketModalWindow from '@/Components/Modals/TicketModalWindow.vue';

export default {
  components: {
    TicketModalWindow,
  },
  props: ["ticket_status"],
  data() {
    return {
      Parameters: new Object(),
      loading: false,
      errored: false,
      user_id: 0,
      isSupporter: false,
    }
  },
  mounted() {
    this.GetURLParameter();
    this.isSupporter = this.$parent.isSupporter;

    // if (this.isSupporter == true) {
    //   this.GetTickets();
    // } else {
    //   setInterval(() => this.GetTickets(), 10000);
    // }
    this.GetTickets();
  },
  methods: {
    GetTickets() {
      // console.log("GetTickets()");
      
      const bx24 = new BX24(window, parent);

      bx24.getAuth().then(auth => {
        axios.post("/api/tickets", {
          token: auth,
          sid: this.Parameters,
          ticket_status: this.ticket_status,
        }).then(response => {
          this.user_id = response.data.user_id;
          let D = response.data.data;

          for (var i = 0; i < D.length; i++) {
            D[i].status = this.user_id == D[i].last_message_user_id
            || this.user_id != D[i].user_id && this.user_id != D[i].response_id && this.isSupporter == true;
          }

          this.$page.props.AllTickets = D;
          // this.QueueSorting();
        }).catch(error => {
          console.log(error);
          this.errored = true;
        });
      });
    },
    QueueSorting() {
      if (this.ticket_status == 0) {
        return;
      }
      const l = this.$page.props.AllTickets.length;
      let users_ids = new Map();
      let lost_ids = [];

      for (var i = 0; i < l; i++) {
        let user_id = this.$page.props.AllTickets[i].response_id;
        if (this.$page.props.AllTickets[i].status == false) {
          lost_ids.push(this.$page.props.AllTickets[i].id);
          continue;
        }

        if (users_ids.has(`${user_id}`)) {
          let x = users_ids.get(`${user_id}`);
          users_ids.set(`${user_id}`, ++x);
          this.$page.props.AllTickets[i].queue_number = x;
        } else {
          users_ids.set(`${user_id}`, 1);
        }
      }

      for (var i = 0; i < l; i++) {
        let user_id = this.$page.props.AllTickets[i].response_id;

        if (lost_ids.includes(this.$page.props.AllTickets[i].id)) {
          if (users_ids.has(`${user_id}`)) {
            let x = users_ids.get(`${user_id}`);
            users_ids.set(`${user_id}`, ++x);
            this.$page.props.AllTickets[i].queue_number = x;
          } else {
            users_ids.set(`${user_id}`, 1);
          }
        }
      }
    },
    ShowCurrentTicketModal: function(ticket) {
      this.$refs.CurrentTicketModal.show = true;
      this.$refs.CurrentTicketModal.ticket = ticket;
    },
    FormatDate(date) {
      let tz = Intl.DateTimeFormat().resolvedOptions().timeZone;
      var aestTime = new Date().toLocaleString("en-US", {timeZone: tz});
      aestTime = new Date(date);
      return aestTime.toLocaleString();
    },
    GetCols() {
      return this.ticket_status == 1 ? "grid-cols-12" : "grid-cols-9";
    },
    ShowBorder(ticket) {
      let data = "";
      data += ticket.active == 1 ? " border-b" : " border-0";
      data += this.ticket_status == 1 ? " grid-cols-12" : " grid-cols-9";
      return data;
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