<template>
  <header v-if="register" class="bg-white shadow">
    <div class="max-w-12xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Регистрация сотрудников</h2>
    </div>
  </header>
  <header v-else-if="move" class="bg-white shadow">
    <div class="max-w-12xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Перемешение сотрудников</h2>
    </div>
  </header>
  <header v-else-if="statistic" class="bg-white shadow">
    <div class="max-w-12xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Статистика по сотрудникам</h2>
    </div>
  </header>
  <header v-else class="bg-white shadow">
    <div class="max-w-12xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Увольнение сотрудников</h2>
    </div>
  </header>

  <div class="bg-gray-50">
    <div class="grid grid-flow-col grid-cols-3 grid-rows-1 gap-2 justify-items-center">
      <div class="my-1">
        <button v-on:click="event('register')" class="float-center bg-transparent hover:bg-green-500 text-green-700 font-semibold hover:text-white py-1 px-1 border border-green-500 hover:border-transparent rounded">
          Зарегистрировать
        </button>
      </div>
      <div class="my-1">
        <button v-on:click="event('move')" class="float-center bg-transparent hover:bg-yellow-500 text-yellow-700 font-semibold hover:text-white py-1 px-1 border border-yellow-500 hover:border-transparent rounded">
          Переместить
        </button>
      </div>
      <div class="my-1">
        <button v-on:click="event('statistic')" class="float-center bg-transparent hover:bg-yellow-500 text-yellow-700 font-semibold hover:text-white py-1 px-1 border border-yellow-500 hover:border-transparent rounded">
          Статистика
        </button>
      </div>
      <div class="my-1">
        <button v-on:click="event('dismiss')" class="float-center bg-transparent hover:bg-red-500 text-red-700 font-semibold hover:text-white py-1 px-1 border border-red-500 hover:border-transparent rounded">
          Уволить
        </button>
      </div>
    </div>
  <div class="flex items-center justify-center space-x-2 animate-bounce" v-if="loading">
    <div class="flex absolute">
      <div class="w-8 h-8 bg-red-500 rounded-full"></div>
      <div class="w-8 h-8 bg-blue-700 rounded-full"></div>
      <div class="w-8 h-8 bg-blue-100 rounded-full"></div>
    </div>
  </div>

  <div v-else>
    <div class="flex flex-col" v-if="register">
      <div class="p-6">
        <form @submit.prevent="submit">
          <div class="my-1">
            <h4>Имя</h4>
            <input id="name"  v-model="form.name" class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm mt-1 block w-full phone-input mt-1 block w-ful" type="text"/>
          </div>
          <div class="my-1">
            <h4>Фамилия</h4>
            <input id="surname" v-model="form.surname" class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm mt-1 block w-full phone-input mt-1 block w-ful" type="text"/>
          </div>
          <div class="my-1">
            <h4>Отчество</h4>
            <input id="patronymic" v-model="form.patronymic" class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm mt-1 block w-full phone-input mt-1 block w-ful" type="text"/>
          </div>
          <div class="my-1">
            <h4>Должность</h4>
            <input id="work_position" v-model="form.work_position" class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm mt-1 block w-full phone-input mt-1 block w-ful" type="text"/>
          </div>
          <div class="my-1">
            <h4>Отдел продаж</h4>
            <select2-multiple-control id="selectSalesDepartment"  :options="salesDepartment" @select="salesDepartmentSelect($event)" />
          </div>
          <div class="my-1">
            <button v-on:click="UserAdd()" class="float-right bg-transparent hover:bg-green-500 text-green-700 font-semibold hover:text-white py-1 px-1 border border-green-500 hover:border-transparent rounded">
              Зарегистрировать
            </button>
          </div>
        </form>
      </div>
    </div>

    <div class="flex flex-col" v-else-if="move">
      <div class="p-6">
        Перенос сотрудников в стадии разработки
      </div>
    </div>

    <div class="flex flex-col" v-else-if="statistic">
      <div class="p-6">
        <table class="min-w-full">
          <thead>
            <tr>
              <th class="px-6 py-3 text-xs font-medium leading-4 tracking-wider text-center text-gray-500 uppercase border-b border-gray-200 bg-gray-50">Город</th>
              <th class="px-6 py-3 text-xs font-medium leading-4 tracking-wider text-center text-gray-500 uppercase border-b border-gray-200 bg-gray-50">РОП</th>
              <th class="px-6 py-3 text-xs font-medium leading-4 tracking-wider text-center text-gray-500 uppercase border-b border-gray-200 bg-gray-50">Мен.</th>
              <th class="px-6 py-3 text-xs font-medium leading-4 tracking-wider text-center text-gray-500 uppercase border-b border-gray-200 bg-gray-50">Дел.</th>
              <th class="px-6 py-3 text-xs font-medium leading-4 tracking-wider text-center text-gray-500 uppercase border-b border-gray-200 bg-gray-50">Стаж.</th>
              <th class="px-6 py-3 text-xs font-medium leading-4 tracking-wider text-center text-gray-500 uppercase border-b border-gray-200 bg-gray-50">Своб. мест</th>
              <th class="px-6 py-3 text-xs font-medium leading-4 tracking-wider text-center text-gray-500 uppercase border-b border-gray-200 bg-gray-50">Раб. мест</th>
              <th class="px-6 py-3 text-xs font-medium leading-4 tracking-wider text-center text-gray-500 uppercase border-b border-gray-200 bg-gray-50">Комментарий</th>
            </tr>
          </thead>
          <tbody class="bg-white">
            <tr v-for="(dep, depIndex) in departmentList" :key="depIndex">
              <td class="px-4 py-3 border-b border-gray-200 text-left">{{dep.NAME}}</td>
              <td class="px-4 py-3 border-b border-gray-200 text-center">{{dep.countROP}}</td>
              <td class="px-4 py-3 border-b border-gray-200 text-center">{{dep.countMENAGER}}</td>
              <td class="px-4 py-3 border-b border-gray-200 text-center">{{dep.countDEL}}</td>
              <td class="px-4 py-3 border-b border-gray-200 text-center">0</td>
              <td class="px-4 py-3 border-b border-gray-200 text-center">0</td>
              <td class="px-4 py-3 border-b border-gray-200 text-center">0</td>
              <td class="px-4 py-3 border-b border-gray-200 text-center">
                <textarea rows="2" cols="45" name="text">Шкуратов Никита: Огромный комментарий</textarea>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div class="flex flex-col" v-else>
      <div class="p-6">
        Увольнение в стадии разработки
      </div>
    </div>

  </div>
  </div>
<Notification :toast="this.$page.props.toast" />
</template>

<script>
import { Head, Link } from '@inertiajs/inertia-vue3'
// import ModalWindow from '@/Components/Other/ModalWindow.vue'
import { BX24 } from 'bx24'
import { DocumentDownloadIcon, EyeIcon } from '@heroicons/vue/solid'
import Select2MultipleControl from 'v-select2-multiple-component';
import Notification from '@/Components/Notification.vue'

export default {
  components: {
      Head,
      Link,
      // ModalWindow,
      DocumentDownloadIcon,
      EyeIcon,
      Select2MultipleControl,
      Notification
    },    
    props: {
      data: new Object()
    },
    data() {
      return {
        loading: false,
        errored: false,
        show: false,
        Parameters: new Object(),
        page: window.location.search.substring(1),
        salesDepartment: [{id: 0, text: 'Пусто'}],
        selectDepartments: [],
        departmentList: [],
        statistic: true,
        register: false,
        move: false,
        dismiss: false,
        form: this.$inertia.form({
            surname: '',
            name: '',
            patronymic: '',
            token: '',
            sid: '',
            work_position: '',
            salesDepartment: [],
        }),
      }
    },
    mounted() {
      this.salesDepartment = this.data.salesDepartment;
    },
    methods: {
      event: function (event) {
        this.move = false;
        this.register = false;
        this.dismiss = false;
        this.statistic = false;

        switch (event) {
          case 'move': this.move = true;
              break;
          case 'dismiss': this.dismiss = true;
              break;
          case 'statistic': 
                this.statistic = true;
                this.UserStat();
              break;
          default: this.register = true;
        }
      },
      UserAdd(){
        const bx24 = new BX24(window, parent);
        this.GetURLParameter();
        var sid = this.Parameters;
        bx24.getAuth().then(auth => {
          let selected = $('#selectSalesDepartment option:checked'),
              values = Array.from(selected).map(el => el.value);

          this.form.token = auth;
          this.form.sid = sid;
          this.form.salesDepartment = values;

          axios.post("/api/user.add", this.form).then(response => {
            this.loading = false;
            let data = response.data;

            return this.$page.props.toast = {
                status: data.status,
                msg: data.msg
            }
          }).catch(error => {
              console.log(error);
              this.errored = true;
          })
        });
      },
      UserStat(){
        const bx24 = new BX24(window, parent);
        this.GetURLParameter();
        var sid = this.Parameters;
        bx24.getAuth().then(auth => {
          let selected = $('#selectSalesDepartment option:checked'),
              values = Array.from(selected).map(el => el.value);

          this.form.token = auth;
          this.form.sid = sid;
          this.form.salesDepartment = values;

          axios.post("/api/user.stat", this.form).then(response => {
            this.departmentList = response.data.data;
            this.loading = false;
          }).catch(error => {
              console.log(error);
              this.errored = true;
          })
        });
      },
      salesDepartmentSelect({id, text}){
        this.selectDepartments.push(id);
      },
      GetURLParameter() {
        var sPageURL = window.location.search.substring(1);
        var sURLVariables = sPageURL.split('&');
        for (var i = 0; i < sURLVariables.length; i++) {
          var sParameterName = sURLVariables[i].split('=');
          this.Parameters[sParameterName[0]] = sParameterName[1];
        }
      }
    }
  }
</script>
<style score>
.select2-container {
    min-width: 99%;
}
</style>