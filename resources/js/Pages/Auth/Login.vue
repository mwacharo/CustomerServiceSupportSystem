<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import AuthenticationCard from "@/Components/AuthenticationCard.vue";
import AuthenticationCardLogo from "@/Components/AuthenticationCardLogo.vue";
import Checkbox from "@/Components/Checkbox.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";

defineProps({
  canResetPassword: Boolean,
  status: String,
});

const form = useForm({
  email: "",
  password: "",
  remember: false,
});

const submit = () => {
  form
    .transform((data) => ({
      ...data,
      remember: form.remember ? "on" : "",
    }))
    .post(route("login"), {
      onFinish: () => form.reset("password"),
    });
};
</script>

<template>
  <v-card class="my-card">
    <v-container fluid class="fill-height">
      <v-row>
        <!-- Left Side (Documentation Info) -->
        <v-col cols="12" md="6" class="green--text text-white" style="background-color: #2596be">
          <div class="pa-10">
            <h1>Boxleo Courier</h1>
            <h2>Customer Service Support </h2>
            <v-btn class="mt-5" color="white" text to="#">
              Make Your Business Better →
            </v-btn>
          </div>
        </v-col>

        <!-- Right Side (Login Form) -->
        <v-col cols="12" md="6" class="d-flex justify-center align-center">
          <v-card class="pa-5" width="400">
            <v-card-title>Log in </v-card-title>
            <v-card-text>
              <v-form @submit.prevent="submit">
                <!-- Email Field -->
                <v-text-field v-model="form.email" label="Email" outlined dense :error-messages="form.errors.email"
                  autocomplete="email" required></v-text-field>

                <!-- Password Field -->
                <v-text-field v-model="form.password" label="Password" type="password" outlined dense
                  :error-messages="form.errors.password" autocomplete="current-password" required></v-text-field>

                <!-- Remember Me Checkbox -->
                <!-- <Checkbox id="remember" v-model="form.remember" label="Remember me" class="mt-2" /> -->

                <!-- Submit Button -->
                <v-btn @click="submit" color="#2596be" block class="mt-3" :loading="form.processing">
                  Log In
                </v-btn>
           <div>     <Link :href="route('register')" class="text-decoration-none" >
                  Sign Up
                  </Link></div>
               
                  <div>
                    <Link :href="route('password.request')" class="text-decoration-none">
                  Forgot your password
                  </Link>
                  </div>
           
              </v-form>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </v-card>
</template>

<style scoped>
.my-card {
  margin: 100px;
}

.fill-height {
  height: 100vh;
}

.text-decoration-none {
  text-decoration: none;
}

.text-blue {
  color: #4CAF50;
  /* Matches the theme */
}

.hover\:underline:hover {
  text-decoration: underline;
}
</style>
