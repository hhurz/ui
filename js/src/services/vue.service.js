import Vue from 'vue';
import SuiVue from 'semantic-ui-vue';

import DatePicker from 'v-calendar/lib/components/date-picker.umd';
import atkInlineEdit from '../components/inline-edit.component';
import itemSearch from '../components/item-search.component';
import multiLine from '../components/multiline.component';
import treeItemSelector from '../components/tree-item-selector/tree-item-selector.component';
import atkClickOutside from '../directives/click-outside.directive';
import VueQueryBuilder from '../components/query-builder/query-builder.component.vue';
import { focus } from '../directives/commons.directive';

Vue.use(SuiVue);
Vue.component('v-date-picker', DatePicker);

const atkComponents = {
    'atk-inline-edit': atkInlineEdit,
    'atk-item-search': itemSearch,
    'atk-multiline': multiLine,
    'atk-tree-item-selector': treeItemSelector,
    'atk-query-builder': VueQueryBuilder,
};

// setup atk custom directives.
const atkDirectives = [{ name: 'click-outside', def: atkClickOutside }, { name: 'focus', def: focus }];
atkDirectives.forEach((directive) => {
    Vue.directive(directive.name, directive.def);
});

/**
 * Singleton class
 * Create Vue component.
 */
class VueService {
    static getInstance() {
        return this.instance;
    }

    constructor() {
        if (!VueService.instance) {
            this.vues = [];
            this.eventBus = new Vue();
            this.vueMixins = {
                methods: {
                    getData: function () {
                        return this.initData;
                    },
                },
                // provide method to our child component.
                // child component would need to inject a method to have access using the inject property,
                // inject: ['getRootData'],
                // Once inject you can get initial data using this.getRootData().
                provide: function () {
                    return {
                        getRootData: this.getData,
                    };
                },
            };
            VueService.instance = this;
        }
        return VueService.instance;
    }

    /**
   * Created a Vue component and add it to the vues array.
   *
   * @param name
   * @param component
   * @param data
   */
    createAtkVue(name, component, data) {
        this.vues.push({
            name: name,
            instance: new Vue({
                el: name,
                data: { initData: data },
                components: { [component]: atkComponents[component] },
                mixins: [this.vueMixins],
            }),
        });
    }

    /**
   * Create a Vue instance from an external src component definition.
   *
   * @param name
   * @param component
   * @param data
   */
    createVue(name, componentName, component, data) {
        this.vues.push({
            name: name,
            instance: new Vue({
                el: name,
                data: { initData: data },
                components: { [componentName]: window[component] },
                mixins: [this.vueMixins],
            }),
        });
    }

    /**
   * Emit an event to the eventBus.
   * Listener to eventBus can respond to emitted event.
   *
   * @param event
   * @param data
   */
    emitEvent(event, data = {}) {
        this.eventBus.$emit(event, data);
    }

    /**
   * Register components within Vue.
   */
    useComponent(component) {
        if (window[component]) {
            Vue.use(window[component]);
            // let vcomponent = Vue.component('SuiInput').extend({props:{isFluid: true}});
            // console.log(vcomponent);
        } else {
            console.error('Unable to register component: ' + component + '. Make sure it is load correctly.');
        }
    }

    /**
   * Return Vue.
   *
   * @returns {Vue | VueConstructor}
   */
    getVue() {
        return Vue;
    }
}

const vueService = new VueService();
Object.freeze(vueService);

export default vueService;
