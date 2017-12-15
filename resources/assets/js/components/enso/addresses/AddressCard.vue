<template>

    <card
        footer
        :header="false"
        :footer-items="3"
        icon="fa fa-map">
        <div class="media has-padding-medium">
            <div class="media-content" :class="address.is_default ? 'is-success' : 'is-warning'">
                <!--default slot content will be overwritten if anything is provided-->
                <slot name="address-card-template" :address="address">
                    <br>
                    <span v-if="address.street">{{__('street')}}: {{ address.street }}</span>
                    <span v-if="address.number">{{__('number')}}: {{ address.number }}</span>
                    <br>

                    <span v-if="address.building">{{__('building')}}: {{ address.building }}</span>
                    <span v-if="address.entry">{{__('entry')}}: {{ address.entry }}</span>
                    <span v-if="address.floor">{{__('floor')}}: {{ address.floor }}</span>
                    <span v-if="address.apartment">{{__('apartment')}}: {{ address.apartment }}</span>
                    <br>
                    <span v-if="address.sub_administrative_area">{{__('subAdministrativeArea')}}: {{ address.sub_administrative_area }}</span>
                    <span v-if="address.city">{{__('city')}}: {{ address.city }}</span>
                    <br>
                    <span v-if="address.postal_area">{{__('postalArea')}}: {{ address.postal_area }}</span>
                    <span v-if="address.administrative_area">{{__('administrativeArea')}}: {{ address.administrative_area }}</span>
                    <br>
                    {{ address.country_name }} <br>
                    <i class="fa fa-sticky-note "></i> {{ address.obs }} <br>
                </slot>
            </div>
        </div>

        <a slot="footer-item-1" >
            <i class="fa fa-pencil-square-o pull-left margin-left-md"
               @click="handleEdit"></i>
        </a>
        <a slot="footer-item-2" >
            <i class="fa fa-anchor"
               @click="setDefault"></i>
        </a>
        <a slot="footer-item-3" >
            <i class="fa fa-trash-o pull-right margin-right-md"
               @click="showModal=true"></i>
        </a>

        <modal :show="showModal"
            @cancel-action="showModal=false"
            @commit-action="destroy()">
        </modal>
    </card>

</template>

<script>

    import { mapGetters } from 'vuex';
    import AddressModalForm from './AddressModalForm';
    import Modal from '../bulma/Modal.vue';
    import Card from '../bulma/Card.vue';
    export default {
        components: {AddressModalForm, Card, Modal},
        props: {
            index: {
                type: Number,
                required: true
            },
            address: {
                type: Object,
                required: true
            },
        },

        data() {
            return {
                form: null,
                showModal: false
            };
        },

        computed: {
            ...mapGetters('locale', ['__']),
        },

        methods: {

            setDefault() {
                axios.get('/addresses/setDefault/' + this.address.id).then(response => {
                    this.$emit('default-set', response.data.message);
                }).catch((error) => {
                    this.reportEnsoException(error);
                });
            },
            handleEdit() {
                this.$emit('do-edit');
            },
            destroy() {
                this.showModal = false;
                this.$emit('do-delete', {index: this.index, id: this.address.id});
            }
        }
    }

</script>

<style>

    address.contact-info {
        padding-left: 5px;
        padding-top: 5px;
    }

    .small-box-footer.controls > i {
        cursor: pointer;
    }

    .modal-body {
        color: #3c3a3a !important;
    }

</style>