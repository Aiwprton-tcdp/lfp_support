<template> 
  <Notification :toast="this.$page.props.toast" />
  <NewTicketModalWindow ref="NewTicketModal"></NewTicketModalWindow>
  <!-- <SlideoutPanel ref="NewTicketModal"></SlideoutPanel> -->
  <NewCouponModalWindow v-if="isSupporter == true" ref="NewCouponModal"></NewCouponModalWindow>
  <ModifyTreeModalWindow v-if="isSupporter == true" ref="ModifyTreeModal"></ModifyTreeModalWindow>

  <header class="absolute">
    <div class="max-w-12xl py-3 px-4 sm:px-6 lg:px-8">
      <h2 v-if="tickets" class="font-semibold text-xl text-gray-800 leading-tight">Тикеты</h2>
      <h2 v-else-if="tickets_archive" class="font-semibold text-xl text-gray-800 leading-tight">Архив</h2>
      <h2 v-else-if="coupons" class="font-semibold text-xl text-gray-800 leading-tight">Купоны</h2>
      <h2 v-else-if="coupons_archive" class="font-semibold text-xl text-gray-800 leading-tight">Архив купонов</h2>
    </div>
  </header>

  <div class="bg-gray-50 p-1 pt-12">
    <div class="justify-items-center m-2">
      <button v-on:click="event('tickets')"
        class="float-center bg-transparent hover:bg-green-500 text-green-700 font-semibold hover:text-white py-1 px-1 border border-green-500 hover:border-transparent rounded">
        Тикеты
      </button>

      <button v-on:click="event('tickets_archive')"
        class="float-center mx-1 bg-transparent hover:bg-yellow-500 text-yellow-700 font-semibold hover:text-white py-1 px-1 border border-yellow-500 hover:border-transparent rounded">
        Архив
      </button>
      
      <template v-if="isSupporter == true">
        <button v-on:click="event('coupons')"
          class="float-center bg-transparent hover:bg-green-500 text-green-700 font-semibold hover:text-white py-1 px-1 border border-green-500 hover:border-transparent rounded">
          Купоны
        </button>
        <button v-on:click="event('coupons_archive')"
          class="float-center mx-1 bg-transparent hover:bg-yellow-500 text-yellow-700 font-semibold hover:text-white py-1 px-1 border border-yellow-500 hover:border-transparent rounded">
          Архив купонов
        </button>
        <button v-on:click="GoToNewСouponModal"
          class="float-right mx-1 bg-transparent hover:bg-green-500 text-green-700 font-semibold hover:text-white py-1 px-1 border border-green-500 hover:border-transparent rounded">
          Создать купон
        </button>
        <button v-on:click="GoToModifyTreeModal"
          class="float-right mx-1 bg-transparent hover:bg-yellow-500 text-yellow-700 font-semibold hover:text-white py-1 px-1 border border-yellow-500 hover:border-transparent rounded">
          Редактировать дерево проблем
        </button>
      </template>

      <template v-else>
        <button v-on:click="GoToNewTicketModal"
          class="float-right mx-1 bg-transparent hover:bg-green-500 text-green-700 font-semibold hover:text-white py-1 px-1 border border-green-500 hover:border-transparent rounded">
          Создать новый тикет
        </button>
      </template>
    </div>

    <div v-if="loading" class="items-center justify-center animate-bounce h-40">
      <div class="flex absolute">
        <div class="w-8 h-8 bg-red-500 rounded-full"></div>
        <div class="w-8 h-8 bg-blue-700 rounded-full"></div>
        <div class="w-8 h-8 bg-blue-100 rounded-full"></div>
      </div>
    </div>

    <div v-else>
      <div class="flex flex-col">
        <div v-if="tickets" class="p-6 h-full">
          <TicketsList :ticket_status="ticket_status"></TicketsList>
        </div>
        <div v-else-if="tickets_archive" class="p-6 h-full">
          <TicketsList :ticket_status="ticket_status"></TicketsList>
        </div>
        <div v-else-if="coupons" class="p-6 h-full">
          <CouponsList :coupon_status="coupon_status"></CouponsList>
        </div>
        <div v-else-if="coupons_archive" class="p-6 h-full">
          <CouponsList :coupon_status="coupon_status"></CouponsList>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Notification from '@/Components/Notification.vue';
import TicketsList from '@/Components/TicketsList.vue';
import CouponsList from '@/Components/CouponsList.vue';
import NewTicketModalWindow from '@/Components/Modals/NewTicketModalWindow.vue';
import NewCouponModalWindow from '@/Components/Modals/NewCouponModalWindow.vue';
import ModifyTreeModalWindow from '@/Components/Modals/ModifyTreeModalWindow.vue';
import Centrifuge from 'centrifuge';

export default {
  components: {
    Notification,
    TicketsList,
    CouponsList,
    NewTicketModalWindow,
    NewCouponModalWindow,
    ModifyTreeModalWindow,
  },
  props: {
    isSupporter: false,
    user_id: 0,
    AllTickets: [],
    AllCoupons: [],
  },
  data() {
    return {
      Parameters: new Object(),
      loading: false,
      errored: false,
      tickets: true,
      tickets_archive: false,
      coupons: false,
      coupons_archive: false,
      ticket_status: 1,
      coupon_status: 1,
    }
  },
  mounted() {
    this.GetURLParameter();

    // if (this.ticket_status == 1) {
    //   this.WebSocketStart();
    // }
  },
  methods: {
    event: function(event) {
      this.tickets = false;
      this.tickets_archive = false;
      this.coupons = false;
      this.coupons_archive = false;
      this.ticket_status = 1;
      this.coupon_status = 1;

      switch (event) {
        case "tickets":
          this.tickets = true;
          break;
        case "tickets_archive":
          this.tickets_archive = true;
          this.ticket_status = 0;
          break;
        case "coupons":
          this.coupons = true;
          break;
        case "coupons_archive":
          this.coupons_archive = true;
          this.coupon_status = 0;
          break;
        default:
          this.tickets = true;
          break;
      }
    },
    WebSocketStart() {
        const centrifuge = new Centrifuge("wss://sms19.ru:1010/connection/websocket", {
          debug: true,
          subscribeEndpoint: window.document.location.origin + "/api/centrifuge/subscribe",
          onRefresh: (ctx, cb) => {
              let promise = fetch(window.document.location.origin + "/api/websocket/refresh", {
                method: "POST",
                user_id: this.currentUserID,
              }).then(resp => {
                resp.json().then(data => {
                    // window.localStorage.setItem('SupportToken', data.token);
                    this.SetCookie("SupportToken", data.token);
                    cb(data.token);
                    centrifuge.setToken(data.token);
                    centrifuge.connect();
                });
              });
          },
        });
        // let token = window.localStorage.getItem('SupportToken') ??
        let token = this.GetCookie("SupportToken") ??
          'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxIiwiZXhwIjoxNjQ5NjU2MTA5fQ.TrDPoOuqyMKVP6G5m2k6Br6TWsDN0FxFRbXaYRSz7e0';
        centrifuge.setToken(token);

        centrifuge.on('disconnect', function(context) {
          console.log(context);
        });

        centrifuge.on('connect', function(context) {
          console.log(context);
        });
console.log(this.$page.AllTickets);
        // this.AllTickets.forEach(e => {
        //   centrifuge.subscribe("public:ticket." + e.id, message => {
        //     // if (message.data.user_id == this.currentUserID) {
        //     //    this.dataMessages.pop();
        //     // }
        //     console.log(message);
        //     // this.dataMessages.push(message.data);
        //     // this.ScrollChatToEnd();
        //   });
        // });

        centrifuge.connect();
    },
    SetCookie(name, value, options = {}) {
        options = {
          path: '/',
          ...options
        };

        if (options.expires instanceof Date) {
          options.expires = options.expires.toUTCString();
        }

        let updatedCookie = encodeURIComponent(name) + "=" + encodeURIComponent(value);

        for (let optionKey in options) {
          updatedCookie += "; " + optionKey;
          let optionValue = options[optionKey];
          if (optionValue !== true) {
              updatedCookie += "=" + optionValue;
          }
        }

        document.cookie = updatedCookie;
    },
    GetCookie(name) {
        let matches = document.cookie.match(new RegExp(
          "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
        ));
        return matches ? decodeURIComponent(matches[1]) : undefined;
    },
    GoToNewTicketModal() {
      this.$refs.NewTicketModal.show = true;
    },
    GoToNewСouponModal() {
      this.$refs.NewCouponModal.show = true;
    },
    GoToModifyTreeModal() {
      this.$refs.ModifyTreeModal.show = true;
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