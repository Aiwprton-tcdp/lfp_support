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
	/* border-radius: 8px; */
	/* padding: 15px; */
  width: 90vw;
  height: 100vh;
	/* min-width: 50vw;
	max-width: 90vw;
	position: absolute;
	top: 50%; */
	/* left: 50%; */
	transform: translate(-99%, 0%);
}
.close {
	border-radius: 50%;
	color: #fff;
	display: flex;
	align-items: center;
	justify-content: center;
	position: absolute;
	top: 7px;
	right: 20px;
	width: 30px;
	height: 30px;
	cursor: pointer;
}
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
  <VueSidePanel v-model="show" @click.self="closeModal" lock-scroll>
    <template #default>
      <div v-if="show" class="modal-shadow">
        <div class="modal h-full p-10">
          <div class="flex content-between mb-4">
            <div>
              <div class="close" @click="closeModal">&#10006;</div>
            </div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Создание тикета</h2>
          </div>

          <div class="flex flex-row grid grid-cols-5 h-full space-x-5">
            <div class="col-span-3">
              <label class="text-left text-gray-700">Выберите наиболее точное описание Вашей проблемы:</label>
              <div class="h-5/6 flex flex-col content-center py-3">
                <div class="overflow-y-auto scrollbar-thumb-blue scrollbar-thumb-rounded scrollbar-track-blue-lighter scrollbar-w-2">
                  <TreeTemplate v-for="r in reasonsTree.children" :key="r.id" :data="r" class="flex"></TreeTemplate>
                </div>
              </div>
            </div>

            <div class="h-4/5 col-span-2">
              <template v-if="areHintsChecked == false">
                <div class="content-center my-4 space-y-3">
                  <label class="text-left text-gray-700">Прежде, чем создать тикет, попробуйте решить проблему самостоятельно:</label>
                  <div class="flex flex-col">
                    <label v-for="(h, key) in hints" :key="key">{{ h }}</label>
                  </div>
                </div>

                <button v-on:click="CheckHints"
                  class="bg-transparent hover:bg-green-500 text-green-700 hover:text-white font-semibold py-2 px-4 border border-green-500 hover:border-transparent rounded">
                  Советы не помогли
                </button>
              </template>

              <template v-else>
                <div class="content-center my-4 space-y-3">
                  <div>
                    <label>Добавьте комментарий для уточнения проблемы:</label>
                    <textarea v-model="description" title="" class="resize-y block text-left rounded-md w-full max-h-40"></textarea>
                  </div>

                  <div>
                    <label>Использовать купон:</label>
                    <input v-model="coupon_id" id="WeightSelect" type="text" maxlength="30" class="shadow appearance-none border rounded text-gray-700 w-full"/>
                  </div>
                </div>

                <button v-on:click="CreateNewTicket"
                  class="bg-transparent hover:bg-green-500 text-green-700 hover:text-white font-semibold py-2 px-4 border border-green-500 hover:border-transparent rounded">
                  Подтвердить
                </button>
              </template>
            </div>
          </div>
        </div>
      </div>
    </template>
  </VueSidePanel>
</template>

<script>
import { BX24 } from 'bx24';
import TreeTemplate from '@/Components/TreeTemplate.vue';
import { VueSidePanel } from 'vue3-side-panel';
import 'vue3-side-panel/dist/vue3-side-panel.css';

export default {
  name: "NewTicketModalWindow",
	components: {
    TreeTemplate,
    VueSidePanel,
  },
  data() {
    return {
      show: false,
      errored: false,
      reasonsTree: { children: [] },
			reason: Object(),
			hints: Array(),
			reason_id: 0,
      description: "",
      coupon_id: "",
      areHintsChecked: true,
    }
  },
  mounted() {
    if (this.$page.props.isSupporter === false) {
      this.GetAllReasons();
    }
  },
  methods: {
		closeModal: function() {
      this.ClearAllData();
      this.show = false;
    },
    CreateNewTicket() {
      const bx24 = new BX24(window, parent);
      // let reg = new RegExp('[0-9]', "");

			if (this.reason_id == 0) {
				return this.$page.props.toast = {
					status: "error",
					message: "Не выбрана причина"
				};
			} else if (this.description.length == 0 || this.description.replace(/\s/g, '') === "") {
				return this.$page.props.toast = {
					status: "error",
					message: "Не указан комментарий"
				};
			} else if (this.coupon_id.replace(/[0-9]/g, "") != "") {
				return this.$page.props.toast = {
					status: "error",
					message: "Номер купона должен состоять из арабских цифр"
				};
			} else if (parseInt(this.coupon_id, 10) == 0) {
				return this.$page.props.toast = {
					status: "error",
					message: "Некорректный номер купона"
				};
			}

      bx24.getAuth().then(auth => {
        axios.post("/api/ticket/new", {
          token: auth,
          sid: this.$parent.Parameters,
          reason: this.reason_id,
          description: this.description,
          coupon_id: parseInt(this.coupon_id, 10),
        }).then(response => {
          if (response.data.status == "success") {
            this.show = false;
            this.ClearAllData();
            let D = response.data.data;
            D.status = true;
            this.$page.props.AllTickets.push(D);
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
    GetAllReasons() {
      axios.get("/api/reasons/get").then(response => {
				this.GetTree(response.data.data);
      }).catch(error => {
        console.log(error);
        this.errored = false;
      });
    },
    GetHints() {
      const bx24 = new BX24(window, parent);

      bx24.getAuth().then(auth => {
        axios.post("/api/hints/get", {
          token: auth,
          sid: this.$parent.Parameters,
          reason_id: this.reason_id,
        }).then(response => {
          this.hints = response.data.data.length > 0
            ? JSON.parse(response.data.data)
            : [];
          this.areHintsChecked = this.hints.length == 0;
        }).catch(error => {
          console.log(error);
          this.errored = true;
        });
      });
    },
    CheckHints() {
      this.areHintsChecked = true;
    },
		GetTree(reasons) {
			reasons.forEach(r => {
				if (r.parent_id == null) {
					this.reasonsTree.children.push({
						id: r.id,
						name: r.name,
						weight: r.weight,
						clicked: false,
						children: [],
					});
				}

				this.reasonsTree.children.forEach(t => t.children = this.IterateTree(reasons, t.id));
			});
		},
		IterateTree(Array, id) {
			let Result = [];
			
			Array.forEach(t => {
				if (t.parent_id != id) {
					return null;
				}

				Result.push({
					id: t.id,
					name: t.name,
          weight: t.weight,
					clicked: false,
					children: this.IterateTree(Array, t.id) ?? [],
				});
			});
			
			return Result;
		},
    ClearAllData() {
      this.ClearSelectInTree(this.reasonsTree.children);
      this.areHintsChecked = true;
      this.description = "";
      this.coupon_id = "";
      this.reason_id = 0;
    },
    ClearSelectInTree(data) {
      data.forEach(d => {
        d.clicked = false;
        if (d.children == []) return;
        this.ClearSelectInTree(d.children);
      });
    },
  }
}
</script>