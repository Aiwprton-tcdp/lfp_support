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
   <div class="h-96">

   <!-- <div class="flex sm:items-center justify-between py-2 border-b-2 border-gray-200">
      <div class="relative flex items-center space-x-4">
         <div class="relative">
            <span class="absolute text-green-500 right-0 bottom-0">
               <svg width="20" height="20">
                  <circle cx="8" cy="8" r="8" fill="currentColor"></circle>
               </svg>
            </span>
         <img src="https://images.unsplash.com/photo-1549078642-b2ba4bda0cdb?ixlib=rb-1.2.1&amp;ixid=eyJhcHBfaWQiOjEyMDd9&amp;auto=format&amp;fit=facearea&amp;facepad=3&amp;w=144&amp;h=144" alt="" class="w-10 sm:w-16 h-10 sm:h-16 rounded-full">
         </div>
         <div class="flex flex-col leading-tight">
            <div class="text-2xl mt-1 flex items-center">
               <span class="text-gray-700 mr-3">Anderson Vanhron</span>
            </div>
            <span class="text-lg text-gray-600">Junior Developer</span>
         </div>
      </div>
      <div class="flex items-center space-x-2">
         <button type="button" class="inline-flex items-center justify-center rounded-lg border h-10 w-10 transition duration-500 ease-in-out text-gray-500 hover:bg-gray-300 focus:outline-none">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="h-6 w-6">
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
         </button>
         <button type="button" class="inline-flex items-center justify-center rounded-lg border h-10 w-10 transition duration-500 ease-in-out text-gray-500 hover:bg-gray-300 focus:outline-none">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="h-6 w-6">
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
            </svg>
         </button>
         <button type="button" class="inline-flex items-center justify-center rounded-lg border h-10 w-10 transition duration-500 ease-in-out text-gray-500 hover:bg-gray-300 focus:outline-none">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="h-6 w-6">
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
            </svg>
         </button>
      </div>
   </div> -->

      <div id="messages" class="h-full space-y-4 p-3 overflow-y-auto">
         <div class="chat-message" v-for="(message, key) in dataMessages" :key="key">
            <div v-if="message.user_id == currentUserID" class="flex items-end justify-end">
               <div class="flex flex-col space-y-2 text-xs max-w-xs mx-2 order-1 items-end">
                  <span v-if="isSupporter" class="sub">{{ message.user_id }}</span>
                  <span class="px-4 py-2 rounded-lg text-base inline-block bg-blue-600 text-white">{{ message.content }}</span>
               </div>
            </div>

            <div v-else class="flex items-end">
               <div class="flex flex-col space-y-2 text-xs max-w-xs mx-2 order-2 items-start">
                  <span v-if="isSupporter" class="sub">{{ message.user_id }}</span>
                  <span class="px-4 py-2 rounded-lg text-base inline-block bg-gray-300">{{ message.content }}</span>
               </div>
            </div>
         </div>
         
         <!-- Удаление файлов -->

         <!-- <div class="inline-block align-bottom bg-green-200">
            <label v-if="files.length > 0" class="my-1">При отправке сообщения также будет отправлен следующий 
               <span v-if="files.length > 1">список документов</span>
               <span v-else>документ</span>:
            </label>
            <div v-for="(file, key) in files" :key="key" class="file-listing">
               {{ file.name }}
               <span class="cursor-pointer text-red-900 hover:text-red-500" v-on:click="RemoveFile(key)"> - удалить</span>
            </div>
         </div> -->
      </div>
   </div>
      
      <template v-if="ticket.active" class="bottom-2">
         <select v-if="isSupporter && currentUserID == ticket.response_id" v-model="message">
            <option disabled value="">Выберите шаблон сообщения</option>
            <option v-for="(m, key) in MessageTemplates" :key="key">{{ m.content }}</option>
         </select>

         <div class="relative flex mt-1 items-center">
            <!-- Отправка файлов -->

            <!-- <label for="files_input" class="cursor-pointer h-6 w-6 text-gray-600">
               <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
               </svg>
            </label>
            <input id="files_input" type="file" ref="files" multiple v-on:change="HandleFileUpload()" class="hidden"/> -->

            <input type="text"
               v-model="message"
               v-on:keyup.enter="SendMessage"
               placeholder="Написать сообщение..."
               class="w-full focus:outline-none focus:placeholder-gray-400 text-gray-600 placeholder-gray-600 bg-gray-200 rounded-md py-2" />

            <div class="absolute right-0 inset-y-0 hidden sm:flex">
               <button type="button" v-on:click="SendMessage"
                  class="inline-flex justify-center rounded-lg p-2 transition duration-500 ease-in-out text-white bg-blue-500 hover:bg-blue-400 focus:outline-none">
                  <span class="font-bold">Отправить</span>
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-6 w-6 ml-2 transform rotate-90">
                     <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"></path>
                  </svg>
               </button>
            </div>
         </div>
      </template>
</template>

<script>
import { BX24 } from 'bx24';
import Centrifuge from 'centrifuge';

export default {
   name: "ChatTemplate",
   components: { },
   props: ["ticket"],
   data: function() {
      return {
         Parameters: new Object(),
         isSupporter: false,
         currentUserID: 0,
         dataMessages: Array(),
         MessageTemplates: Array(),
         message: "",
         files: [],
         old_files: [],
         filesOrder: [],
         filesFinish: [],
         fileProgress: 0,
         fileCurrent: ""
      }
   },
   created() {
      this.GetMessages();
   },
   mounted() {
      this.GetMessageTemplates();
   },
   methods: {
      GetMessages() {
         const bx24 = new BX24(window, parent);
         this.GetURLParameter();

         bx24.getAuth().then(auth => {
               axios.post("/api/ticket/messages", {
                  token: auth,
                  sid: this.Parameters,
                  ticket_id: this.ticket.id
               }).then(response => {
                  this.dataMessages = response.data.messages;
                  this.isSupporter = response.data.isSupporter;
                  this.currentUserID = response.data.currentUserID;
                  this.WebSocketStart();
               }).catch(error => {
                  console.error(error);
                  this.errored = true;
               }).finally(() => this.ScrollChatToEnd());
         });
      },
      GetMessageTemplates() {
         const bx24 = new BX24(window, parent);
         this.GetURLParameter();

         bx24.getAuth().then(auth => {
               axios.post("/api/ticket/message_templates", {
                  token: auth,
                  sid: this.Parameters,
               }).then(response => {
                  console.log(response.data);
                  console.log(this.MessageTemplates);
                  this.MessageTemplates = response.data.messages;
                  console.log(this.MessageTemplates);
               }).catch(error => {
                  console.error(error);
                  this.errored = true;
               });
         });
      },
      SendMessage() {
         if ((this.message.length == 0 || this.message.replace(/\s/g, '') === "") && this.files.length == 0) {
            return this.$page.props.toast = {
               status: "error",
               message: "Вы не ввели сообщение"
            }
         }
         if (this.files.length > 0) {
            this.PrepareFiles();
         }

         const bx24 = new BX24(window, parent);

         bx24.getAuth().then(auth => {
            axios.post('/api/ticket/message/add', {
               token: auth,
               sid: this.Parameters,
               ticket_id: this.ticket.id,
               message: this.message,
            }).then(response => {
               this.message = "";
               // this.dataMessages.push(response.data.data);

               return this.$page.props.toast = {
                  status: response.data.status,
                  message: response.data.message
               }
            }).catch(error => {
               console.error(error);
               this.errored = true;
            }).finally(() => this.ScrollChatToEnd());
         });
      },
      async PrepareFiles() {
         this.fileIsLoading = true;
         for (let item of this.files) {
            await this.SendFile(item);
         }
         this.fileIsLoading = false;
         this.filesFinish = [];
         this.files = [];
      },
      async SendFile(item) {
         let form = new FormData();
         const bx24 = new BX24(window, parent);
         
         form.append("item", item);

         await bx24.getAuth().then(auth => {
            axios.post('/api/ticket/file/add', {
               token: auth,
               sid: this.Parameters,
               ticket_id: this.ticket.id,
               form: item,
               headers: {
                  'Content-Type': 'multipart/form-data;'
               }
            }).then(response => {
               if (response.status != 200) {
                  return this.$page.props.toast = {
                     status: "error",
                     message: "Не удалось загрузить файл"
                  }
               }

               this.dataMessages.push(response.data.data);
               this.fileProgress = 0;
               this.fileCurrent = "";
               this.filesFinish.push(item);
               // this.filesOrder.splice(item, 1);
            }).catch(error => {
               console.error(error);
               this.errored = true;
            });
         });
      },
      HandleFileUpload() {
         let newFiles = Array.from(event.target.files);

         newFiles.forEach(f => {
            if (this.files.length == 10) {
               return this.$page.props.toast = {
                  status: "error",
                  message: "За раз Вы можете отправить не более 10 файлов"
               }
            } else {
               this.files.push(f);
            }
         });

         this.ScrollChatToEnd();
      },
      SubmitFiles() {
         let formData = new FormData();
         for(let i = 0; i < this.files.length; i++) {
            let file = this.files[i];
            formData.append('files[' + i + ']', file);
         }
         return formData;
      },
      RemoveFile(key) {
         this.files.splice(key, 1);
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
            console.log("disconnected");
            console.log(context);
         });

         centrifuge.on('connect', function(context) {
            console.log("connected");
            console.log(context);
         });

         centrifuge.subscribe("public:ticket." + this.ticket.id, message => {
            // if (message.data.user_id == this.currentUserID) {
            //    this.dataMessages.pop();
            // }
            this.dataMessages.push(message.data);
            this.ScrollChatToEnd();
         });

         centrifuge.connect();
      },
      ScrollChatToEnd() {
         const el = document.getElementById("messages");
         setTimeout(() => {
            el.scrollTop = el.scrollHeight;
         }, 1);
      },
      GetURLParameter() {
         let sPageURL = window.location.search.substring(1),
            sURLVariables = sPageURL.split('&');

         for (let i = 0; i < sURLVariables.length; i++) {
            let sParameterName = sURLVariables[i].split('=');
            this.Parameters[sParameterName[0]] = sParameterName[1];
         }
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
   }
}
</script>