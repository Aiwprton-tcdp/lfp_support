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
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Редактирование дерева проблем</h2>
          </div>
          
          <div class="flex flex-row grid grid-cols-5 h-full space-x-5">
            <div class="h-full col-span-3">
              <label class="text-left text-gray-700">Выберите наиболее точное структурное расположение предполагаемой проблемы:</label>
              <div class="h-96 flex flex-col py-3 overflow-y-auto">
                <template v-for="reasons in reasonsTree.children" :key="reasons.id">
                  <TreeTemplate :data="reasons" class="flex"></TreeTemplate>
                </template>

                <span v-on:click="EditReason" class="cursor-pointer">Добавить корневую причину: [+]</span>

                <div v-if="isEditable == true" class="inset-x-0 p-2 content-between">
                  <div class="flex flex-col">
                    <div class="flex flex-row">
                      <input v-model="name" maxlength="50" class="shadow appearance-none border rounded w-96 py-2 px-3 text-gray-700"/>
                      <select v-model="weight" id="WeightSelect" class="form-select w-fit w-14 py-2">
                        <option v-for="w in weightes" :value="w" :key="w">{{ w }}</option>
                      </select>
                      <select v-model="group_name" class="form-select w-fit w-20 py-2">
                        <option v-for="(g, key) in groups" :value="g" :key="key" :selected="group_id">{{ g }}</option>
                      </select>
                    </div>

                    <div class="flex flex-row content-between">
                      <button v-on:click="AddReason()" class="float-center bg-transparent text-blue-600 hover:text-blue-900 font-semibold py-1 px-1">
                        Сохранить
                      </button>
                      <span> | </span>
                      <button v-on:click="CancelEditReason" class="float-center bg-transparent text-red-600 hover:text-red-900 font-semibold py-1 px-1">
                        Отменить
                      </button>
                    </div>
                  </div>
                </div>
              </div>
              
              <div class="mt-8">
                <button v-on:click="AddNewReason" class="float-center bg-transparent text-blue-600 hover:text-blue-900 font-semibold py-1 px-1">
                  Подтвердить
                </button>
                <button v-on:click="RefreshReasons" class="float-center bg-transparent text-red-600 hover:text-red-900 font-semibold py-1 px-1">
                  Отменить
                </button>
              </div>
            </div>

            <div v-if="reason_id != 0 && reason_id != null" class="col-span-2">
              <div class="content-center my-4 space-y-2">
                <label class="text-left text-gray-700">Советы для выбранной проблемы:</label>
                <div class="h-72 overflow-y-auto">
                  <template v-for="(h, key) in newHints" :key="key">
                    <div class="grid grid-cols-12 gap-2 content-center">
                      <input type="text" :value="h" class="resize-no block text-left rounded-md col-span-10">
                      <button v-on:click="RemoveCurrentHint(key)" class="float-center bg-transparent text-red-600 hover:text-red-900 font-semibold py-1 px-1">
                        <span style="font-size:2em">&#215;</span>
                      </button>
                    </div>
                  </template>
                </div>

<!-- tickets: reason_id
reasons: parent_id -> reasons.id; group_id
additional_responsives: ticket_id
histories: ticket_id; coupon_id; reason_id
hints: reason_id
messages: ticket_id
operations: activity_id; history_id


-->
                <div class="grid grid-cols-12 gap-2">
                  <input type="text" v-model="newHint" placeholder="Новый совет..." class="col-span-10">
                  <button v-on:click="SaveCurrentHint"
                    class="float-center bg-transparent text-green-600 hover:text-green-900 font-semibold py-1 px-1">
                    <span style="font-size:2em">&#43;</span>
                  </button>
                </div>

                <button v-on:click="SaveHints"
                  class="bg-transparent hover:bg-green-500 text-green-700 hover:text-white font-semibold py-2 px-4 border border-green-500 hover:border-transparent rounded">
                  Сохранить советы
                </button>
              </div>
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
  name: "ModifyTreeModalWindow",
	components: {
    TreeTemplate,
    VueSidePanel,
  },
  data() {
    return {
      show: false,
      errored: false,
      reasonsTree: { children: [] },
      newReasons: Array(),
      modifiedReasons: Array(),
			hints: Array(),
			newHint: "",
			newHints: Array(),
			reason: Object(),
			reason_id: 0,

      isEditable: false,
      weightes: Array.from({length: 10}, (_, i) => i + 1),
      name: "",
      weight: 0,
      groups: ["Админ", "Сисадмин", "Программист"],
      group_id: 0,
      group_name: "",
    }
  },
  mounted() {
    if (this.$page.props.isSupporter === true) {
      this.GetAllReasons();
    }
    this.group_name = this.groups[0];
  },
  methods: {
		closeModal: function() {
      this.show = false;
      this.RefreshReasons();
    },
    RefreshReasons() {
      if (this.newReasons.length == 0) {
        return;
      }
      this.newReasons = [];
      this.reasonsTree.children = [];
      if (this.$page.props.isSupporter === true) {
        this.GetAllReasons();
      }
      this.newHints = [];
    },
    EditReason() {
      this.name = "";
      this.weight = 0;
      this.isEditable = true;
      this.group_name = this.groups[this.group_id - 1];
    },
    CancelEditReason() {
      this.isEditable = false;
    },
    AddReason() {
      if (this.isSupporter === false) {
        this.isEditable = false;
				return this.$page.props.toast = {
					status: "error",
					message: "У Вас недостаточно прав для данной операции"
				};
      } else if (this.name.length == 0 || this.name.replace(/\s/g, '') === "") {
				return this.$page.props.toast = {
					status: "error",
					message: "Не указано описание проблемы"
				};
			} else if (this.weight == 0) {
				return this.$page.props.toast = {
					status: "error",
					message: "Не указана сложность проблемы"
				};
			}
      let GroupID = this.groups.indexOf(this.group_name);

      let reason = {
        "name": this.name,
        "weight": this.weight,
        "group_id":  GroupID + 1,
        "parent_id": null,
        "active": 1,
      };
      // parent.newReasons.push(reason);
      // let reason1 = reason;
      // reason1.id = null;
      // this.data.children.push(reason1);
      this.newReasons.push(reason);
      reason.id = null;
      this.reasonsTree.children.push(reason);
      this.isEditable = false;
    },
    AddNewReason() {
			if (this.newReasons.length == 0 && this.modifiedReasons.length == 0) {
				return this.$page.props.toast = {
					status: "error",
					message: "Недостаточно данных для сохранения"
				};
			}
// console.log(this.newReasons);
// console.log(this.modifiedReasons);
//       // let y = this.modifiedReasons.reverse();
//       let y = this.modifiedReasons.sort(function(a, b) {
//         return a.id - b.id;
//       });
//       // let x = [...new Set(this.modifiedReasons.map(x => [x.id, x])).values()];
//       // let x = this.modifiedReasons.groupBy(({ id }) => id);
//       let sorted = [];
//       let x1 = y.reduce((a, b, i, y) => a.id == b.id ? sorted.push(b) : sorted.push(a));
//       // let x1 = this.modifiedReasons.sort(function(a, b) {
//       //   return a.id - b.id;
//       // });
// console.log(y);
// // console.log(x);
// console.log(x1);
// console.log(sorted);
// return;
      const bx24 = new BX24(window, parent);

      bx24.getAuth().then(auth => {
        axios.post("/api/reasons/add", {
          token: auth,
          sid: this.$parent.Parameters,
          reasons: this.newReasons,
        }).then(response => {
          this.show = false;
          this.RefreshReasons();

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
    GetHints() {
      if (this.reason_id == 0 || this.reason_id == null) {
        return;
      }
      const bx24 = new BX24(window, parent);

      bx24.getAuth().then(auth => {
        axios.post("/api/hints/get", {
          token: auth,
          sid: this.$parent.Parameters,
          reason_id: this.reason_id,
        }).then(response => {
          if (response.data.data.length == 0) {
            this.hints = [];
            this.newHints = [];
            return;
          }

          this.hints = JSON.parse(response.data.data);
          this.newHints = this.hints;
        }).catch(error => {
          console.log(error);
          this.errored = true;
        });
      });
    },
    SaveCurrentHint() {
      if (this.newHint.replaceAll(" ", "") == "") {
				return this.$page.props.toast = {
					status: "error",
					message: "Недостаточно данных для сохранения"
				};
      }
      
      this.newHints.push(this.newHint.trim());
      this.newHint = "";
    },
    RemoveCurrentHint(id) {
      let newArray = [];

      for (let i = 0; i < this.newHints.length; i++) {
        if (i == id) {
          continue;
        }
        newArray.push(this.newHints[i]);
        console.log(i);
      }
      
      this.newHints = newArray;
    },
    CheckHints() {
      return JSON.stringify(this.hints) === JSON.stringify(this.newHints);
    },
    SaveHints() {
			if (this.CheckHints() == true) {
				return this.$page.props.toast = {
					status: "error",
					message: "Вы ничего не изменили"
				};
			}

      const bx24 = new BX24(window, parent);

      bx24.getAuth().then(auth => {
        axios.post("/api/hints/edit", {
          token: auth,
          sid: this.$parent.Parameters,
          reason_id: this.reason_id,
          hints: this.newHints,
        }).then(response => {
          if (response.data.status == "success") {
            this.newHints = [];
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
		GetTree(reasons) {
			reasons.forEach(r => {
				if (r.parent_id == null) {
					this.reasonsTree.children.push({
						id: r.id,
						name: r.name,
						weight: r.weight,
						group_id: r.group_id,
						parent_id: r.parent_id,
						active: r.active == 1,
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
          group_id: t.group_id,
          active: t.active == 1,
					clicked: false,
					children: this.IterateTree(Array, t.id) ?? [],
				});
			});
			
			return Result;
		},
  }
}
</script>