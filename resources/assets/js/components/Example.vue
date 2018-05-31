<template>

<button v-on:click="counter += 1">{{ counter }}</button>

</template>






<script>

var data = { counter: 0 }

    module.exports = {
      data: function () {
        return {
            counter: 0
        }
      }
    }


</script>
