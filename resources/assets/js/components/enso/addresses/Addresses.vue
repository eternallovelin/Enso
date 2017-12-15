<template>

    <card icon="fa fa-map-signs"
        refresh search
        :title="title || __('Addresses')"
        :overlay="loading"
        @refresh="get()"
        :open="open"
        ref="addressCard"
        @query-update="query = $event"
        :badge="addresses.length"
        :controls="1">

        <a slot="control-1" class="card-header-icon">
            <span class="icon is-small"
                  @click="create()">
                <i class="fa fa-plus-square"></i>
            </span>
        </a>

        <div class="has-padding-medium contacts-wrapper">
            <div class="columns is-multiline">
                <address-card v-for="(address, index) in filteredAddresses"
                    class="column is-half-tablet is-one-third-widescreen address-card"
                    :address="address"
                    @default-set="handleDefaultSet"
                    @do-edit="handleEdit(address)"
                    @do-delete="destroy"
                    :index="index"
                    :key="index">

                        <template slot="address-card-template" :address="address">
                            <slot name="address-template" :address="address">
                            </slot>
                        </template>

                </address-card>
            </div>
        </div>
        <address-modal-form
            v-if="form"
            :form="form"
            :type="type"
            :id="id"
            ref="modal"
            @patch="get();form=null"
            @form-close="form=null"
            @form-submit="get();form=null"
            @delete="get();form=null"
            @store="get();form=null">




        </address-modal-form>
    </card>

</template>

<script>

    import { mapGetters } from 'vuex';
    import Card from '../bulma/Card.vue';
    import AddressCard from './AddressCard';
    import AddressModalForm from './AddressModalForm';
    export default {
        name: 'Addresses',
        components: {AddressCard, AddressModalForm, Card},
        props: {
            id: {
                type: Number,
                required: true
            },
            type: {
                type: String,
                required: true
            },
            theme: {
                type: String,
                default: 'primary'
            },
            solid: {
                type: Boolean,
                default: false,
            },
            open: {
                type: Boolean,
                default: false
            },
            title: {
                type: String,
                default: null
            }
        },

        computed: {
            ...mapGetters('locale', ['__']),
            filteredAddresses() {
                return this.query
                    ? this.addresses.filter(address => {
                        return address.city.toLowerCase().indexOf(this.query.toLowerCase()) > -1
                            || address.street.toLowerCase().indexOf(this.query.toLowerCase()) > -1
                            || address.number.toLowerCase().indexOf(this.query.toLowerCase()) > -1;
                    })
                    : this.addresses;
            },
        },

        data() {
            return {
                loading: false,
                query: '',
                addresses: [],
                bodyStyle: {'max-height': '415px', 'overflow-y': 'auto'},
                form:null,
            };
        },

        methods: {

            getEditForm(address) {
                axios.get('/api/addresses/getEditForm/' + address.id).then(response => {
                    this.$emit('form-loaded', response.data);
                    this.form = response.data;
                }).catch( error => {
                    this.handleError(error);
                });
            },
            getCreateForm() {
                const params = {addressable_id: this.id, addressable_type: this.type};
                axios.get('/api/addresses/getCreateForm', {params: params}).then(response => {
                    this.form = response.data.createForm;
                }).catch( error => {
                    this.handleError(error);
                });
            },
            handleEdit(address) {
                this.getEditForm(address);
            },
            handleDefaultSet($event) {
                this.get();
                toastr.success($event);
            },
            get() {
                this.loading = true;

                axios.get('/api/addresses/list', { params: { id: this.id, type: this.type } }).then(response => {
                    this.addresses = response.data;
                    this.loading = false;
                }).catch(error => {
                    this.loading = false;
                    this.handleError(error);
                });
            },
            create() {

                if (this.$refs.addressCard.collapsed) {
                    this.$refs.addressCard.toggle();
                }

                this.getCreateForm();
            },
            destroy(payload) {

                axios.delete('/addresses/' + payload.id).then(response => {
                    this.$parent.loading = false;
                    this.addresses.splice(payload.index,1);
                }).catch(error => {
                    this.$parent.loading = false;
                    this.handleError(error);
                });
            }
        },

        mounted() {
            this.get();
        }
    }

</script>

<style>

    .contacts-wrapper {
        max-height: 415px;
        overflow-y: auto;
    }

    .address-card {
        /*border: 1px solid #4a4a4a;*/
        margin: 10px;
    }

</style>