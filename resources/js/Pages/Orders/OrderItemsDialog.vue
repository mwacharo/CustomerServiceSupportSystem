<template>
    <v-dialog v-model="dialog" max-width="900px">
      <v-card>
        <v-card-title class="text-h5 bg-primary text-white">
          Order Details: {{ currentOrder ? currentOrder.order_no : '' }}
          <v-spacer></v-spacer>
          <v-btn icon @click="closeDialog">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>
  
        <v-card-text class="pt-4">
          <!-- Order Items Table -->
          <v-data-table
            :headers="itemHeaders"
            :items="orderItems"
            :loading="loading"
            class="elevation-1 mt-4"
          >
            <template v-slot:item.actions="{ item }">
              <v-btn icon small class="mr-2" @click="editItem(item)">
                <v-icon>mdi-pencil</v-icon>
              </v-btn>
              <v-btn icon small color="error" @click="confirmDeleteItem(item)">
                <v-icon>mdi-delete</v-icon>
              </v-btn>
            </template>
          </v-data-table>
        </v-card-text>
  
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="primary" @click="closeDialog">Close</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  
    <!-- Edit Item Dialog -->
    <v-dialog v-model="editDialog" max-width="500px">
      <v-card>
        <v-card-title class="text-h5">Edit Item</v-card-title>
        <v-card-text>
          <v-container>
            <v-row>
              <v-col cols="12">
                <v-text-field
                  v-model="editedItem.name"
                  label="Product Name"
                  required
                ></v-text-field>
              </v-col>
              <v-col cols="6">
                <v-text-field
                  v-model.number="editedItem.quantity"
                  label="Quantity"
                  type="number"
                  min="1"
                  required
                ></v-text-field>
              </v-col>
              <v-col cols="6">
                <v-text-field
                  v-model="editedItem.price"
                  label="Price"
                  prefix="$"
                  required
                ></v-text-field>
              </v-col>
              <v-col cols="12">
                <v-text-field
                  v-model="editedItem.sku_no"
                  label="SKU"
                  required
                ></v-text-field>
              </v-col>
            </v-row>
          </v-container>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="blue-darken-1" text @click="closeEditDialog">Cancel</v-btn>
          <v-btn color="blue-darken-1" text @click="saveItem">Save</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  
    <!-- Delete Confirmation Dialog -->
    <v-dialog v-model="deleteDialog" max-width="400px">
      <v-card>
        <v-card-title class="text-h5">Delete Item</v-card-title>
        <v-card-text>
          Are you sure you want to delete this item?
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="blue-darken-1" text @click="closeDeleteDialog">Cancel</v-btn>
          <v-btn color="error" text @click="deleteItem">Delete</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </template>
  
  <script>
  export default {
    props: {
      value: {
        type: Boolean,
        default: false
      },
      order: {
        type: Object,
        default: () => null
      }
    },
    
    data() {
      return {
        dialog: false,
        editDialog: false,
        deleteDialog: false,
        loading: false,
        currentOrder: null,
        orderItems: [],
        editedIndex: -1,
        editedItem: {
          id: null,
          name: '',
          quantity: 1,
          price: '',
          sku_no: '',
          total_price: ''
        },
        defaultItem: {
          id: null,
          name: '',
          quantity: 1,
          price: '',
          sku_no: '',
          total_price: ''
        },
        itemHeaders: [
          { title: 'Product', key: 'name', align: 'start' },
          { title: 'SKU', key: 'sku_no', align: 'start' },
          { title: 'Quantity', key: 'quantity', align: 'center' },
          { title: 'Price', key: 'price', align: 'end' },
          { title: 'Total', key: 'total_price', align: 'end' },
          { title: 'Actions', key: 'actions', sortable: false, align: 'center' }
        ]
      }
    },
    
    watch: {
      value(val) {
        this.dialog = val
      },
      dialog(val) {
        if (!val) this.$emit('input', false)
        else this.loadOrderItems()
      },
      order(newOrder) {
        if (newOrder) {
          this.currentOrder = JSON.parse(JSON.stringify(newOrder))
          this.loadOrderItems()
        }
      }
    },
    
    methods: {
      loadOrderItems() {
        if (!this.currentOrder) return
        
        this.loading = true
        
        // Map order items to add name and other properties
        if (this.currentOrder.order_items && this.currentOrder.order_items.length) {
          // You'll need to implement your API call here if needed
          // For now, we'll just transform the data
          this.orderItems = this.currentOrder.order_items.map(item => {
            return {
              id: item.id,
              name: item.product_name || `Product #${item.product_id}`, // You might need to fetch product name
              quantity: item.quantity,
              price: item.price,
              sku_no: item.sku_no,
              total_price: item.total_price,
              product_id: item.product_id,
              // Add any other fields you need
            }
          })
        } else {
          this.orderItems = []
        }
        
        this.loading = false
      },
      
      closeDialog() {
        this.dialog = false
        this.$emit('input', false)
      },
      
      editItem(item) {
        this.editedIndex = this.orderItems.indexOf(item)
        this.editedItem = Object.assign({}, item)
        this.editDialog = true
      },
      
      closeEditDialog() {
        this.editDialog = false
        this.$nextTick(() => {
          this.editedItem = Object.assign({}, this.defaultItem)
          this.editedIndex = -1
        })
      },
      
      async saveItem() {
        try {
          // Calculate total price
          this.editedItem.total_price = (parseFloat(this.editedItem.price) * parseInt(this.editedItem.quantity)).toFixed(2)
          
          // Here you would make an API call to update the item
          // const response = await this.$axios.put(`/api/order-items/${this.editedItem.id}`, this.editedItem)
          
          // Update local data
          if (this.editedIndex > -1) {
            Object.assign(this.orderItems[this.editedIndex], this.editedItem)
          }
          
          this.$emit('item-updated', this.editedItem)
          this.closeEditDialog()
        } catch (error) {
          console.error('Error updating item:', error)
          // Handle error - show notification, etc.
        }
      },
      
      confirmDeleteItem(item) {
        this.editedIndex = this.orderItems.indexOf(item)
        this.editedItem = Object.assign({}, item)
        this.deleteDialog = true
      },
      
      closeDeleteDialog() {
        this.deleteDialog = false
        this.$nextTick(() => {
          this.editedItem = Object.assign({}, this.defaultItem)
          this.editedIndex = -1
        })
      },
      
      async deleteItem() {
        try {
          // Here you would make an API call to delete the item
          // const response = await this.$axios.delete(`/api/order-items/${this.editedItem.id}`)
          
          // Remove from local data
          this.orderItems.splice(this.editedIndex, 1)
          
          this.$emit('item-deleted', this.editedItem)
          this.closeDeleteDialog()
        } catch (error) {
          console.error('Error deleting item:', error)
          // Handle error - show notification, etc.
        }
      }
    }
  }
  </script>
  
  <style scoped>
  .v-data-table :deep(th) {
    font-weight: bold;
    background-color: #f5f5f5;
  }
  </style>