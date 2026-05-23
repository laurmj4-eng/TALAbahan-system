<template>
  <div v-if="isCancelled" class="cancellation-label">
    <span
      v-if="isCancelledByCustomer"
      class="label label-danger"
      :title="cancelReason"
    >
      ⚠ CANCELLED BY CUSTOMER
    </span>
    <span v-else class="label label-secondary" :title="cancelReason">
      Cancelled by Admin
    </span>
  </div>
  <div v-else class="status-label">
    <span class="badge" :class="`badge-${getStatusClass(status)}`">
      {{ status }}
    </span>
  </div>
</template>

<script>
export default {
  name: 'OrderCancellationStatus',
  props: {
    status: {
      type: String,
      required: true,
    },
    cancelReason: {
      type: String,
      default: '',
    },
  },
  computed: {
    isCancelled() {
      return this.status === 'Cancelled';
    },
    isCancelledByCustomer() {
      return (
        this.isCancelled &&
        this.cancelReason.toLowerCase().includes('customer')
      );
    },
  },
  methods: {
    getStatusClass(status) {
      const statusMap = {
        'Pending': 'warning',
        'Processing': 'info',
        'Completed': 'success',
        'Cancelled': 'danger',
        'Refunded': 'secondary',
      };
      return statusMap[status] || 'secondary';
    },
  },
};
</script>

<style scoped>
.cancellation-label,
.status-label {
  display: inline-block;
}

.label {
  display: inline-block;
  padding: 0.5rem 0.75rem;
  border-radius: 4px;
  font-size: 0.875rem;
  font-weight: 600;
  white-space: nowrap;
}

.label-danger {
  background-color: #dc3545;
  color: white;
  box-shadow: 0 2px 4px rgba(220, 53, 69, 0.3);
  animation: pulse-danger 2s infinite;
}

.label-secondary {
  background-color: #6c757d;
  color: white;
}

.badge {
  display: inline-block;
  padding: 0.375rem 0.625rem;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
}

.badge-warning {
  background-color: #ffc107;
  color: #212529;
}

.badge-info {
  background-color: #17a2b8;
  color: white;
}

.badge-success {
  background-color: #28a745;
  color: white;
}

.badge-danger {
  background-color: #dc3545;
  color: white;
}

.badge-secondary {
  background-color: #6c757d;
  color: white;
}

@keyframes pulse-danger {
  0%,
  100% {
    opacity: 1;
  }
  50% {
    opacity: 0.8;
  }
}
</style>
