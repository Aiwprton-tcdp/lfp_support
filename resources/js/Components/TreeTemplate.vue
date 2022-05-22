<style>
.item {
  cursor: pointer;
  display: ruby !important;
  line-height: 1.5;
}
.bold {
  font-weight: 600;
}
.clicked {
  color: #00aa00;
}
.arrow {
  display: block;
  border-color: transparent;
  border-top-color: black;
	border-style: solid;
	border-width: .5em .31em 0;
	width: 0;
	height: 0;
  line-height: 1.5;
}
.up {
  border-width: 0 .31em .5em;
  border-top-color: transparent;
  border-bottom-color: black;
}
.left {
  border-width: .31em 0.5em .31em .0;
  border-top-color: transparent;
  border-right-color: black;
}
.right {
  border-width: .31em 0 .31em .5em;
  border-top-color: transparent;
  border-left-color: black;
}
input:checked ~ .dot {
  transform: translateX(100%);
  background-color: #48bb78;
}
</style>

<template>
  <div class="flex flex-row">
    <label v-if="isFolder" v-on:click="Toggle" class="item flex align-middle items-center bold">
      {{ data.name }}
      <i class="arrow" :class="{ up: isOpen }"></i>
    </label>
    <label v-else v-on:click="SelectReason(data)" class="item" :class="{ clicked: data.clicked }">
      {{ data.name }}
    </label>

    <span v-if="isSupporter && data.id != null" v-on:click="EditReason(data.group_id)" class="cursor-pointer">&nbsp;&nbsp;[+]</span>
    
    <!-- <input type="checkbox" v-model="data.active" v-on:click="ChangeActive(data)"/> -->
    <!-- <Checkbox :checked="data.active" v-on:click="ChangeActive(data)"/> -->
    <!-- <VueToggle title="" name="vue-toggle" toggled="data.active" v-on:click="ChangeActive(data)" fontSize="2px"/> -->
  </div>

  <div v-if="isEditable == true" class="inset-x-0 p-2 content-between">
    <div class="flex flex-col">
      <div class="flex flex-row mx-0">
        <input v-model="name" maxlength="50" class="shadow appearance-none border rounded w-80 py-2 px-3 text-gray-700"/>
        <select v-model="weight" class="form-select w-fit w-14 py-2">
          <option v-for="w in weightes" :value="w" :key="w">{{ w }}</option>
        </select>
        <select v-model="group_name" class="form-select w-fit w-20 py-2">
          <option v-for="(g, key) in groups" :value="g" :key="key" :selected="group_id">{{ g }}</option>
        </select>
      </div>

      <div class="flex flex-row content-between">
        <button v-on:click="AddReason(data)" class="float-center bg-transparent text-blue-600 hover:text-blue-900 font-semibold py-1 px-1">
          Сохранить
        </button>
        <span> | </span>
        <button v-on:click="CancelEditReason" class="float-center bg-transparent text-red-600 hover:text-red-900 font-semibold py-1 px-1">
          Отменить
        </button>
      </div>
    </div>
  </div>
  <!-- <div v-if="isEditable == true" class="inset-x-0 p-2 content-between">
    <input v-model="name" maxlength="50" class="shadow appearance-none border rounded w-1/2 py-2 px-3 text-gray-700 mb-3"/>
    <select v-model="weight" id="WeightSelect" class="form-select w-fit w-14 py-2">
      <option v-for="w in weightes" :value="w" :key="w">{{ w }}</option>
    </select>

    <button v-on:click="AddReason(data)" class="float-center bg-transparent text-blue-600 hover:text-blue-900 font-semibold py-1 px-1">
      Сохранить
    </button>
    <span> | </span>
    <button v-on:click="CancelEditReason" class="float-center bg-transparent text-red-600 hover:text-red-900 font-semibold py-1 px-1">
      Отменить
    </button>
  </div> -->
  
  <div v-show="isOpen" v-if="isFolder" class="ml-5">
    <!-- recursively render -->
    <TreeTemplate v-for="data in data.children" :key="data.id" :data="data" class="item"></TreeTemplate>
  </div>
</template>


<script>
// import VueToggle from 'vue-toggle-component';

export default {
  components: {
    // VueToggle,
},
  name: "TreeTemplate",
  props: {
    data: Object
  },
  data() {
    return {
      isOpen: false,
      isSupporter: false,
      isEditable: false,
      weightes: Array.from({length: 10}, (_, i) => i + 1),
      groups: ["Админ", "Сисадмин", "Программист"],
      group_id: 0,
      group_name: "",
      name: "",
      weight: 0,
      ok: true,
    }
  },
  mounted: function() {
    this.isSupporter = this.$page.props.isSupporter;
    this.group_name = this.groups[0];
  },
  computed: {
    isFolder() {
      return this.data.children && this.data.children.length;
    }
  },
  methods: {
    Toggle() {
      if (this.isFolder) {
        this.isOpen = !this.isOpen;
      }
    },
    SelectReason(data) {
      // if (this.isSupporter === true) {
      //   return;
      // }

      let parent = this.GetParent(this),
        tree = parent.reasonsTree;

      if (tree == null) {
        return this.$page.props.toast = {
					status: "error",
					message: "Непредвиденная ошибка. Попробуйте перезагрузить страницу"
				};
      }

      parent.reason_id = data.id;
      if (data.id != null) {
        parent.GetHints();
      }

      try {
        this.ClearSelectInTree(tree.children);
      }
      catch {}

      data.clicked = !data.clicked;
    },
    EditReason(group_id) {
      this.name = "";
      this.weight = 0;
      this.isEditable = true;
      this.group_name = this.groups[group_id - 1];
    },
    CancelEditReason() {
      this.isEditable = false;
    },
    ChangeActive(data) {
      if (data.id == null) {
        return;
      }

      let parent = this.GetParent(this);
      let new_data = {
        id: data.id,
        active: !data.active,
      };

      parent.modifiedReasons.push(new_data);
    },
    AddReason(data) {
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
			} else if (this.group_name == "") {
				return this.$page.props.toast = {
					status: "error",
					message: "Не указана группа для проблемы"
				};
			}

      let parent = this.GetParent(this);
      let GroupID = this.groups.indexOf(this.group_name);
      let reason = {
        "name": this.name,
        "weight": this.weight,
        "group_id": GroupID + 1,
        "parent_id": data.id,
        "active": 1,
      };
      parent.newReasons.push(reason);
      let reason1 = reason;
      reason1.id = null;
      this.data.children.push(reason1);
      this.isEditable = false;
      parent.reason_id = 0;

      // parent.reason_id = data.id;
      // this.ClearSelectInTree(tree.children);
      // data.clicked = !data.clicked;
    },
    GetParent(e, counter = 0) {
      if (counter > 10) {
        return null;
      } else if ("reasonsTree" in e) {
        return e;
      } else {
        return this.GetParent(e.$parent, ++counter);
      }
    },
    ClearSelectInTree(data) {
      data.forEach(d => {
        d.clicked = false;
        if (d.children.length == []) return;
        // if (d.children.length == 0 || d == undefined) return;
        this.ClearSelectInTree(d.children);
      });
    },
  }
}
</script>