<template>
  <div class="sidebar-box" :class="[ editMode ? 'edit' : '' ]">
    <notifications group="main" position="bottom right" />

    <div class="w-100 dt">
      <div class="sidebar-box-title">
        <h3>{{ $t('people.pets_title') }}</h3>
      </div>
      <div v-if="pets.length > 0" class="dtc" :class="[ dirltr ? 'tr' : 'tl' ]">
        <a v-if="!editMode" class="pointer" href="" @click.prevent="editMode = true">
          {{ $t('app.edit') }}
        </a>
        <a v-else class="pointer" href="" @click.prevent="resetState">
          {{ $t('app.done') }}
        </a>
      </div>
    </div>

    <!-- Add button when box is empty -->
    <p v-if="pets.length === 0 && !addMode" class="mb0">
      <a class="pointer" href="" @click.prevent="toggleAdd">
        {{ $t('app.add') }}
      </a>
    </p>

    <!-- List of pets -->
    <ul v-if="pets.length > 0">
      <li v-for="(pet, i) in pets" :key="pet.id" class="mb2">
        <div v-show="!pet.edit" class="w-100 dt">
          <div class="dtc">
            {{ $t('people.pets_' + pet.category_name) }}
            <span v-if="pet.name">
              - {{ pet.name }}
            </span>
          </div>
          <div v-if="editMode" class="dtc" :class="[ dirltr ? 'tr' : 'tl' ]">
            <em class="fa fa-pencil-square-o pointer pr2" @click="toggleEdit(pet)"></em>
            <em class="fa fa-trash-o pointer" @click="trash(pet)"></em>
          </div>
        </div>

        <!-- Pet edit form -->
        <div v-show="pet.edit" class="w-100">
          <form class="measure center">
            <div class="mt3">
              <label :for="'edit-category' + i" class="db fw6 lh-copy f6">
                {{ $t('people.pets_kind') }}
              </label>
              <select :id="'edit-category' + i" v-model="updateForm.pet_category_id" class="db w-100 h2">
                <option v-for="petCategory in petCategories" :key="petCategory.id" :value="petCategory.id">
                  {{ $t('people.pets_' + petCategory.name) }}
                </option>
              </select>
            </div>
            <div class="mt3">
              <label :for="'edit-name' + i" class="db fw6 lh-copy f6">
                {{ $t('people.pets_name') }}
              </label>
              <input :id="'edit-name' + i" v-model="updateForm.name" class="pa2 db w-100" type="text" @keyup.enter="update(pet)" />
            </div>
            <div class="lh-copy mt3">
              <a class="btn btn-primary" href="" @click.prevent="update(pet)">
                {{ $t('app.save') }}
              </a>
              <a class="btn" href="" @click.prevent="toggleEdit(pet)">
                {{ $t('app.cancel') }}
              </a>
            </div>
          </form>
        </div>
      </li>
      <li v-if="editMode && !addMode">
        <a class="pointer" href="" @click.prevent="toggleAdd">
          {{ $t('app.add') }}
        </a>
      </li>
    </ul>

    <!-- Pet Add form -->
    <div v-if="addMode">
      <form class="measure center">
        <div class="mt3">
          <label for="add-category" class="db fw6 lh-copy f6">
            {{ $t('people.pets_kind') }}
          </label>
          <select id="add-category" v-model="createForm.pet_category_id" class="db w-100 h2">
            <option v-for="petCategory in petCategories" :key="petCategory.id" :value="petCategory.id">
              {{ $t('people.pets_' + petCategory.name) }}
            </option>
          </select>
        </div>
        <div class="mt3">
          <label for="add-name" class="db fw6 lh-copy f6">
            {{ $t('people.pets_name') }}
          </label>
          <input id="add-name" v-model="createForm.name" class="pa2 db w-100" type="text" @keyup.enter="store"
                 @keyup.esc="resetState"
          />
        </div>
        <div class="lh-copy mt3">
          <a class="btn btn-primary" href="" @click.prevent="store">
            {{ $t('app.add') }}
          </a>
          <a class="btn" href="" @click.prevent="resetState">
            {{ $t('app.cancel') }}
          </a>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
export default {

  props: {
    hash: {
      type: String,
      default: '',
    },
  },

  data() {
    return {
      petCategories: [],
      pets: [],

      editMode: false,
      addMode: false,
      updateMode: false,

      createForm: {
        pet_category_id: '',
        name: '',
        errors: []
      },

      updateForm: {
        id: '',
        pet_category_id: '',
        name: '',
        edit: false,
        errors: []
      },
    };
  },

  computed: {
    dirltr() {
      return this.$root.htmldir === 'ltr';
    }
  },

  mounted() {
    this.prepareComponent();
  },

  methods: {
    prepareComponent() {
      this.getPetCategories();
      this.getPets();
    },

    getPetCategories() {
      axios.get('petcategories')
        .then(response => {
          this.petCategories = response.data;
        });
    },

    getPets() {
      axios.get('people/' + this.hash + '/pets')
        .then(response => {
          this.pets = response.data;
        });
    },

    store() {
      axios.post('people/' + this.hash + '/pets', this.createForm)
        .then(response => {
          this.addMode = false;
          this.pets.push(response.data);
          this.createForm.name = '';

          this.$notify({
            group: 'main',
            title: this.$t('people.pets_create_success'),
            text: '',
            type: 'success'
          });
        });
    },

    resetState() {
      this.editMode = false;
      this.addMode = false;
    },

    toggleAdd() {
      this.addMode = true;
      this.editMode = true;
      this.createForm.data = '';
      this.createForm.pet_category_id = '';
    },

    toggleEdit(pet) {
      Vue.set(pet, 'edit', !pet.edit);
      this.updateForm.id = pet.id;
      this.updateForm.name = pet.name;
      this.updateForm.pet_category_id = pet.pet_category_id;
    },

    update(pet) {
      axios.put('people/' + this.hash + '/pets/' + pet.id, this.updateForm)
        .then(response => {
          Vue.set(pet, 'edit', !pet.edit);
          Vue.set(pet, 'name', response.data.name);
          Vue.set(pet, 'pet_category_id', response.data.pet_category_id);
          Vue.set(pet, 'category_name', response.data.category_name);

          this.$notify({
            group: 'main',
            title: this.$t('people.pets_update_success'),
            text: '',
            type: 'success'
          });
        });
    },

    trash(pet) {
      axios.delete('people/' + this.hash + '/pets/' + pet.id)
        .then(response => {
          this.getPets();

          this.$notify({
            group: 'main',
            title: this.$t('people.pets_delete_success'),
            text: '',
            type: 'success'
          });
        });

      if (this.pets.length <= 1) {
        this.resetState();
      }
    },
  }
};
</script>
