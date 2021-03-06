<template>

    <div>
        <h5 class="title is-5"
            v-if="data.icon || data.title">
            <span class="icon"
                v-if="data.icon">
                <i :class="data.icon"></i>
            </span>
            <span
                v-if="data.title">
                {{ data.title }}
            </span>
            <hr>
        </h5>
        <form @submit.prevent="submit()"
            class="vue-form">
            <div class="columns is-multiline">
                <div v-for="field in data.fields"
                    class="column"
                    :key="field.name"
                    :class="columnSize"
                    v-if="!field.meta.hidden">
                    <div class="field">
                        <label class="label">
                            {{ __(field.label) }}
                            <p v-if="errors.has(field.name)"
                                class="help is-danger is-pulled-right">
                                {{ errors.get(field.name) }}
                            </p>
                        </label>
                        <span v-if="field.meta.custom">
                            <slot :name="field.name"
                                :field="field"
                                :errors="errors">
                            </slot>
                        </span>
                        <span v-else>
                            <vue-form-input v-if="field.meta.type === 'input'"
                                :field="field"
                                @update="errors.clear(field.name)"
                                :has-error="errors.has(field.name)">
                            </vue-form-input>
                            <vue-select v-if="field.meta.type === 'select'"
                                :has-error="errors.has(field.name)"
                                @input="errors.clear(field.name);"
                                v-model="field.value"
                                :options="field.meta.options"
                                :key-map="field.meta.keyMap"
                                :source="field.meta.source"
                                :multiple="field.meta.multiple"
                                :disabled="field.meta.disabled">
                            </vue-select>
                            <datepicker v-if="field.meta.type === 'datepicker'"
                                @input="errors.clear(field.name)"
                                v-model="field.value"
                                :format="field.meta.format"
                                :time="field.meta.time"
                                :disabled="field.meta.disabled">
                            </datepicker>
                            <datepicker v-if="field.meta.type === 'timepicker'"
                                @input="errors.clear(field.name)"
                                v-model="field.value"
                                :format="field.meta.format"
                                time-only
                                :disabled="field.meta.disabled">
                            </datepicker>
                            <div class="control has-icons-right"
                                v-if="field.meta.type === 'textarea'">
                                <textarea @input="errors.clear(field.name)"
                                    class="textarea"
                                    :class="{ 'is-danger': errors.has(field.name) }"
                                    v-model="field.value"
                                    :rows="field.meta.rows"
                                    :disabled="field.meta.disabled">
                                </textarea>
                                <span class="icon is-small is-right has-text-danger"
                                    v-if="errors.has(field.name)">
                                    <i class="fa fa-warning"></i>
                                </span>
                            </div>
                        </span>
                    </div>
                </div>
            </div>
            <hr>
            <button class="button"
                v-if="data.actions.destroy"
                :class="data.actions.destroy.button.class"
                @click.prevent="showModal = true">
                <span>{{ __(data.actions.destroy.button.label) }}</span>
                <span class="icon">
                    <i :class="data.actions.destroy.button.icon"></i>
                </span>
            </button>
            <button class="button"
                :class="data.actions.create.button.class"
                @click.prevent="create()"
                v-if="data.actions.create">
                <span>{{ __(data.actions.create.button.label) }}</span>
                <span class="icon">
                    <i :class="data.actions.create.button.icon"></i>
                </span>
            </button>
            <button type="submit"
                class="button is-pulled-right"
                :class="[data.actions.store ? data.actions.store.button.class : data.actions.update.button.class, { 'is-loading': loading }]"
                v-if="data.actions.store || data.actions.update">
                <span v-if="data.actions.store">
                    <span>{{ __(data.actions.store.button.label) }}</span>
                    <span class="icon">
                        <i :class="data.actions.store.button.icon"></i>
                    </span>
                </span>
                <span v-else-if="data.actions.update">
                    <span>{{ __(data.actions.update.button.label) }}</span>
                    <span class="icon">
                        <i :class="data.actions.update.button.icon"></i>
                    </span>
                </span>
            </button>
            <div class="is-clearfix"></div>
        </form>
        <modal v-if="data.actions.destroy"
            :show="showModal"
            :__="__"
            :message="data.actions.destroy.button.message"
            @cancel-action="showModal = false"
            @commit-action="destroy()">
        </modal>
    </div>

</template>

<script>

import { mapGetters } from 'vuex';
import Errors from '../../../classes/enso/Errors.js';
import Modal from './Modal.vue';
import VueSelect from '../select/VueSelect.vue';
import Datepicker from '../bulma/Datepicker.vue';
import VueFormInput from './VueFormInput.vue';

export default {
    components: {
        Modal, VueSelect, Datepicker, VueFormInput,
    },

    props: {
        data: {
            type: Object,
            required: true,
        },
        params: {
            type: Object,
            default: null,
        },
    },

    data() {
        return {
            loading: false,
            showModal: false,
            errors: new Errors(),
        };
    },

    computed: {
        ...mapGetters('locale', ['__']),
        columnSize() {
            return `is-${parseInt(12 / this.data.columns, 10)}`;
        },
        path() {
            return this.data.method === 'post'
                ? this.data.actions.store.path
                : this.data.actions.update.path;
        },
    },

    methods: {
        create() {
            this.$emit('create');
            this.$router.push({ name: this.data.actions.create.route });
        },
        submit() {
            this.loading = true;

            axios[this.data.method](this.path, this.formData()).then(({ data }) => {
                this.loading = false;
                toastr.success(data.message);
                this.$emit('submit');

                if (data.redirect) {
                    this.$router.push({
                        name: data.redirect,
                        params: { id: data.id },
                    });
                }
            }).catch((error) => {
                const { status, data } = error.response;
                this.loading = false;

                if (status === 422) {
                    this.errors.set(data.errors);

                    return;
                }

                this.handleError(error);
            });
        },
        formData() {
            return this.data.fields.reduce((object, field) => {
                object[field.name] = field.value;
                return object;
            }, { _params: this.params });
        },
        destroy() {
            this.showModal = false;
            this.loading = true;

            axios.delete(this.data.actions.destroy.path).then(({ data }) => {
                this.loading = false;
                toastr.success(data.message);
                this.$emit('destroy');

                if (data.redirect) {
                    this.$router.push({ name: data.redirect });
                }
            }).catch((error) => {
                this.loading = false;
                this.handleError(error);
            });
        },
    },
};

</script>

<style>

    form.vue-form {
        margin-bottom: 0;
    }

</style>
