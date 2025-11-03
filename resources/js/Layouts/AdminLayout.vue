<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import { Link, usePage, router } from "@inertiajs/vue3";
import { usePermissions } from "@/composables/usePermissions";
import { useTranslations } from "@/composables/useTranslations";
import { useDarkMode } from "@/composables/useDarkMode";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";

const showingSidebar = ref(false);
const isMobile = ref(false);
const showSettingsMenu = ref(true);
const showUserDropdown = ref(false);
const showLanguageDropdown = ref(false);
const page = usePage();

const user = page.props.auth.user;
const { can, hasRole } = usePermissions();
const { t, locale } = useTranslations();
const { isDark, toggleDarkMode, initTheme } = useDarkMode();

// Language options
const languages = {
  id: { name: "Bahasa Indonesia" },
  en: { name: "English" },
  mb: { name: "Bahasa Melayu" },
};

// Function switch language
const switchLanguage = (newLocale) => {
  showLanguageDropdown.value = false;
  router.visit(`/language/${newLocale}`, {
    preserveState: false,
    preserveScroll: true,
    only: ["locale", "translations"],
  });
};

const toggleSettingsMenu = () => {
  showSettingsMenu.value = !showSettingsMenu.value;
};

const toggleUserDropdown = () => {
  showUserDropdown.value = !showUserDropdown.value;
};

const toggleLanguageDropdown = () => {
  showLanguageDropdown.value = !showLanguageDropdown.value;
};

const checkMobile = () => {
  isMobile.value = window.innerWidth < 1024;
  if (window.innerWidth >= 1024) {
    showingSidebar.value = true;
  } else {
    showingSidebar.value = false;
  }
};

const toggleSidebar = () => {
  showingSidebar.value = !showingSidebar.value;
};

const closeSidebarOnMobile = (event) => {
  if (isMobile.value && showingSidebar.value) {
    showingSidebar.value = false;
  }
};

// Close dropdown when clicking outside
onMounted(() => {
  initTheme();
  checkMobile();
  window.addEventListener("resize", checkMobile);
  document.addEventListener("click", (e) => {
    if (!e.target.closest(".user-dropdown-container")) {
      showUserDropdown.value = false;
    }
    if (!e.target.closest(".language-dropdown-container")) {
      showLanguageDropdown.value = false;
    }
  });
});

onUnmounted(() => {
  window.removeEventListener("resize", checkMobile);
});
</script>

<template>
  <div
    class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50 dark:from-slate-900 dark:to-slate-800"
  >
    <!-- Mobile Overlay -->
    <div
      v-if="showingSidebar && isMobile"
      @click="closeSidebarOnMobile"
      class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden"
    ></div>

    <!-- Sidebar -->
    <aside
      :class="[
        'fixed inset-y-0 left-0 z-50 bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-700 shadow-xl transition-all duration-300 ease-in-out',
        isMobile && showingSidebar ? 'translate-x-0' : '',
        isMobile && !showingSidebar ? '-translate-x-full' : '',
        !isMobile ? 'lg:translate-x-0' : '',
        isMobile ? 'w-80' : showingSidebar ? 'w-72' : 'w-20',
      ]"
    >
      <div class="flex flex-col h-full">
        <!-- Logo Section -->
        <div
          class="flex items-center justify-between h-16 lg:h-20 px-6 border-b border-slate-100 dark:border-slate-700"
        >
          <div class="flex items-center space-x-3">
            <img
              src="/icon.png"
              alt="Bilikbecakap Logo"
              class="w-10 h-10 object-cover rounded-xl shadow-sm flex-shrink-0"
            />
            <div v-if="showingSidebar" class="min-w-0">
              <h2 class="text-xl font-bold text-slate-800 dark:text-white truncate">
                Bilikbecakap
              </h2>
              <p class="text-xs text-slate-500 dark:text-slate-400">Dashboard Panel</p>
            </div>
          </div>
          <button
            v-if="isMobile && showingSidebar"
            @click="toggleSidebar"
            class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors lg:hidden"
          >
            <font-awesome-icon icon="times" class="w-5 h-5" />
          </button>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
          <!-- Main Navigation -->
          <div class="space-y-1">
            <Link
              href="/dashboard"
              @click="isMobile ? toggleSidebar() : null"
              :class="[
                'group flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 border',
                $page.url === '/dashboard'
                  ? 'bg-gradient-to-r from-blue-50 to-purple-50 dark:from-blue-900/30 dark:to-purple-900/30 text-blue-700 dark:text-blue-400 border-blue-100 dark:border-blue-800'
                  : 'text-slate-600 dark:text-slate-300 hover:bg-gradient-to-r hover:from-blue-50 hover:to-purple-50 dark:hover:from-blue-900/20 dark:hover:to-purple-900/20 hover:text-blue-700 dark:hover:text-blue-400 border-transparent hover:border-blue-100 dark:hover:border-blue-800',
              ]"
              :title="!showingSidebar ? 'Dashboard' : ''"
            >
              <font-awesome-icon
                icon="tachometer-alt"
                class="w-5 h-5 mr-3 flex-shrink-0"
              />
              <span v-if="showingSidebar">Dashboard</span>
            </Link>
            <!-- Menu Kamus -->
            <Link
              v-if="can('view kamus')"
              href="/admin/kamus"
              @click="isMobile ? toggleSidebar() : null"
              :class="[
                'group flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 border',
                $page.url.startsWith('/admin/kamus')
                  ? 'bg-gradient-to-r from-blue-50 to-purple-50 dark:from-blue-900/30 dark:to-purple-900/30 text-blue-700 dark:text-blue-400 border-blue-100 dark:border-blue-800'
                  : 'text-slate-600 dark:text-slate-300 hover:bg-gradient-to-r hover:from-blue-50 hover:to-purple-50 dark:hover:from-blue-900/20 dark:hover:to-purple-900/20 hover:text-blue-700 dark:hover:text-blue-400 border-transparent hover:border-blue-100 dark:hover:border-blue-800',
              ]"
              :title="!showingSidebar ? 'Kamus' : ''"
            >
              <font-awesome-icon icon="book" class="w-5 h-5 mr-3 flex-shrink-0" />
              <span v-if="showingSidebar">Kamus</span>
            </Link>
            <!-- Menu Translate -->
            <Link
              href="/translate-test"
              @click="isMobile ? toggleSidebar() : null"
              :class="[
                'group flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 border',
                $page.url.startsWith('/translate')
                  ? 'bg-gradient-to-r from-blue-50 to-purple-50 dark:from-blue-900/30 dark:to-purple-900/30 text-blue-700 dark:text-blue-400 border-blue-100 dark:border-blue-800'
                  : 'text-slate-600 dark:text-slate-300 hover:bg-gradient-to-r hover:from-blue-50 hover:to-purple-50 dark:hover:from-blue-900/20 dark:hover:to-purple-900/20 hover:text-blue-700 dark:hover:text-blue-400 border-transparent hover:border-blue-100 dark:hover:border-blue-800',
              ]"
              :title="!showingSidebar ? 'Translate' : ''"
            >
              <font-awesome-icon icon="language" class="w-5 h-5 mr-3 flex-shrink-0" />
              <span v-if="showingSidebar">Translate</span>
            </Link>
            <!-- Menu Artikel -->
            <Link
              v-if="can('view artikel')"
              href="/admin/artikel"
              @click="isMobile ? toggleSidebar() : null"
              :class="[
                'group flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 border',
                $page.url.startsWith('/admin/artikel')
                  ? 'bg-gradient-to-r from-blue-50 to-purple-50 dark:from-blue-900/30 dark:to-purple-900/30 text-blue-700 dark:text-blue-400 border-blue-100 dark:border-blue-800'
                  : 'text-slate-600 dark:text-slate-300 hover:bg-gradient-to-r hover:from-blue-50 hover:to-purple-50 dark:hover:from-blue-900/20 dark:hover:to-purple-900/20 hover:text-blue-700 dark:hover:text-blue-400 border-transparent hover:border-blue-100 dark:hover:border-blue-800',
              ]"
              :title="!showingSidebar ? 'Artikel' : ''"
            >
              <font-awesome-icon icon="newspaper" class="w-5 h-5 mr-3 flex-shrink-0" />
              <span v-if="showingSidebar">Artikel</span>
            </Link>

            <!-- Menu Kuis -->
            <Link
            v-if="can('view quiz')"
            href="/admin/quiz"
            @click="isMobile ? toggleSidebar() : null"
            :class="[
              'group flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 border',
              ($page.url === '/admin/quiz' || 
              ($page.url.startsWith('/admin/quiz/') && 
                !$page.url.includes('/quiz-test')))
                ? 'bg-gradient-to-r from-blue-50 to-purple-50 dark:from-blue-900/30 dark:to-purple-900/30 text-blue-700 dark:text-blue-400 border-blue-100 dark:border-blue-800'
                : 'text-slate-600 dark:text-slate-300 hover:bg-gradient-to-r hover:from-blue-50 hover:to-purple-50 dark:hover:from-blue-900/20 dark:hover:to-purple-900/20 hover:text-blue-700 dark:hover:text-blue-400 border-transparent hover:border-blue-100 dark:hover:border-blue-800'
            ]"
            :title="!showingSidebar ? 'Kuis' : ''"
          >
            <font-awesome-icon icon="question-circle" class="w-5 h-5 mr-3 flex-shrink-0" />
            <span v-if="showingSidebar">Kuis</span>
          </Link>

            <!-- Menu Test Quiz -->
            <Link
              v-if="can('view test quiz')"
              href="/admin/quiz-test"
              @click="isMobile ? toggleSidebar() : null"
              :class="[
                'group flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 border',
                ($page.url === '/admin/quiz-test' || $page.url.startsWith('/admin/quiz-test/'))
                  ? 'bg-gradient-to-r from-blue-50 to-purple-50 dark:from-blue-900/30 dark:to-purple-900/30 text-blue-700 dark:text-blue-400 border-blue-100 dark:border-blue-800'
                  : 'text-slate-600 dark:text-slate-300 hover:bg-gradient-to-r hover:from-blue-50 hover:to-purple-50 dark:hover:from-blue-900/20 dark:hover:to-purple-900/20 hover:text-blue-700 dark:hover:text-blue-400 border-transparent hover:border-blue-100 dark:hover:border-blue-800'
              ]"
              :title="!showingSidebar ? 'Test Quiz' : ''"
            >
              <font-awesome-icon icon="question-circle" class="w-5 h-5 mr-3 flex-shrink-0" />
              <span v-if="showingSidebar">Test Quiz</span>
            </Link>

            <!-- Menu Modul Pembelajaran -->
            <Link v-if="can('view modul pembelajaran')" href="/admin/modul-pembelajaran" @click="isMobile ? toggleSidebar() : null" :class="[
                'group flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 border',
                $page.url.startsWith('/admin/modul-pembelajaran')
                    ? 'bg-gradient-to-r from-blue-50 to-purple-50 dark:from-blue-900/30 dark:to-purple-900/30 text-blue-700 dark:text-blue-400 border-blue-100 dark:border-blue-800'
                    : 'text-slate-600 dark:text-slate-300 hover:bg-gradient-to-r hover:from-blue-50 hover:to-purple-50 dark:hover:from-blue-900/20 dark:hover:to-purple-900/20 hover:text-blue-700 dark:hover:text-blue-400 border-transparent hover:border-blue-100 dark:hover:border-blue-800'
            ]" :title="!showingSidebar ? 'Modul Pembelajaran' : ''">
                <font-awesome-icon icon="book-open" class="w-5 h-5 mr-3 flex-shrink-0" />
                <span v-if="showingSidebar">Modul Pembelajaran</span>
            </Link>

            <Link
              v-if="can('view data master')"
              href="/data-master"
              @click="isMobile ? toggleSidebar() : null"
              :class="[
                'group flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 border',
                $page.url.startsWith('/data-master')
                  ? 'bg-gradient-to-r from-blue-50 to-purple-50 dark:from-blue-900/30 dark:to-purple-900/30 text-blue-700 dark:text-blue-400 border-blue-100 dark:border-blue-800'
                  : 'text-slate-600 dark:text-slate-300 hover:bg-gradient-to-r hover:from-blue-50 hover:to-purple-50 dark:hover:from-blue-900/20 dark:hover:to-purple-900/20 hover:text-blue-700 dark:hover:text-blue-400 border-transparent hover:border-blue-100 dark:hover:border-blue-800',
              ]"
              :title="!showingSidebar ? 'Data Master' : ''"
            >
              <font-awesome-icon icon="database" class="w-5 h-5 mr-3 flex-shrink-0" />
              <span v-if="showingSidebar">Data Master</span>
            </Link>
          </div>

          <!-- Settings Section -->
          <div class="pt-6">
            <p
              v-if="showingSidebar"
              class="px-4 text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-3"
            >
              Settings
            </p>
            <div class="space-y-1">
              <Link
                v-if="can('view users')"
                href="/users"
                @click="isMobile ? toggleSidebar() : null"
                :class="[
                  'group flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 border',
                  $page.url.startsWith('/users')
                    ? 'bg-gradient-to-r from-blue-50 to-purple-50 dark:from-blue-900/30 dark:to-purple-900/30 text-blue-700 dark:text-blue-400 border-blue-100 dark:border-blue-800'
                    : 'text-slate-600 dark:text-slate-300 hover:bg-gradient-to-r hover:from-blue-50 hover:to-purple-50 dark:hover:from-blue-900/20 dark:hover:to-purple-900/20 hover:text-blue-700 dark:hover:text-blue-400 border-transparent hover:border-blue-100 dark:hover:border-blue-800',
                ]"
                :title="!showingSidebar ? 'Users' : ''"
              >
                <font-awesome-icon icon="users" class="w-5 h-5 mr-3 flex-shrink-0" />
                <span v-if="showingSidebar">{{ t("messages.users") }}</span>
              </Link>

              <Link
                v-if="can('view roles')"
                href="/roles"
                @click="isMobile ? toggleSidebar() : null"
                :class="[
                  'group flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 border',
                  $page.url.startsWith('/roles')
                    ? 'bg-gradient-to-r from-blue-50 to-purple-50 dark:from-blue-900/30 dark:to-purple-900/30 text-blue-700 dark:text-blue-400 border-blue-100 dark:border-blue-800'
                    : 'text-slate-600 dark:text-slate-300 hover:bg-gradient-to-r hover:from-blue-50 hover:to-purple-50 dark:hover:from-blue-900/20 dark:hover:to-purple-900/20 hover:text-blue-700 dark:hover:text-blue-400 border-transparent hover:border-blue-100 dark:hover:border-blue-800',
                ]"
                :title="!showingSidebar ? 'Roles' : ''"
              >
                <font-awesome-icon icon="shield-alt" class="w-5 h-5 mr-3 flex-shrink-0" />
                <span v-if="showingSidebar">{{ t("messages.roles") }}</span>
              </Link>

              <Link
                v-if="hasRole('super-admin')"
                href="/permissions"
                @click="isMobile ? toggleSidebar() : null"
                :class="[
                  'group flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 border',
                  $page.url.startsWith('/permissions')
                    ? 'bg-gradient-to-r from-blue-50 to-purple-50 dark:from-blue-900/30 dark:to-purple-900/30 text-blue-700 dark:text-blue-400 border-blue-100 dark:border-blue-800'
                    : 'text-slate-600 dark:text-slate-300 hover:bg-gradient-to-r hover:from-blue-50 hover:to-purple-50 dark:hover:from-blue-900/20 dark:hover:to-purple-900/20 hover:text-blue-700 dark:hover:text-blue-400 border-transparent hover:border-blue-100 dark:hover:border-blue-800',
                ]"
                :title="!showingSidebar ? 'Permissions' : ''"
              >
                <font-awesome-icon icon="lock" class="w-5 h-5 mr-3 flex-shrink-0" />
                <span v-if="showingSidebar">{{ t("messages.permissions") }}</span>
              </Link>

              <Link
                v-if="hasRole('super-admin')"
                href="/activity-logs"
                @click="isMobile ? toggleSidebar() : null"
                :class="[
                  'group flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 border',
                  $page.url.startsWith('/activity-logs')
                    ? 'bg-gradient-to-r from-blue-50 to-purple-50 dark:from-blue-900/30 dark:to-purple-900/30 text-blue-700 dark:text-blue-400 border-blue-100 dark:border-blue-800'
                    : 'text-slate-600 dark:text-slate-300 hover:bg-gradient-to-r hover:from-blue-50 hover:to-purple-50 dark:hover:from-blue-900/20 dark:hover:to-purple-900/20 hover:text-blue-700 dark:hover:text-blue-400 border-transparent hover:border-blue-100 dark:hover:border-blue-800',
                ]"
                :title="!showingSidebar ? 'Activity Logs' : ''"
              >
                <font-awesome-icon icon="history" class="w-5 h-5 mr-3 flex-shrink-0" />
                <span v-if="showingSidebar">{{ t("messages.activity_log") }}</span>
              </Link>
            </div>
          </div>
        </nav>

        <!-- Back to Home Button (Bottom) -->
        <div
          v-if="showingSidebar"
          class="p-4 border-t border-slate-100 dark:border-slate-700"
        >
          <a
            href="/"
            class="flex items-center justify-center px-4 py-3 text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-gradient-to-r hover:from-blue-50 hover:to-purple-50 dark:hover:from-blue-900/20 dark:hover:to-purple-900/20 hover:text-blue-700 dark:hover:text-blue-400 rounded-xl transition-all duration-200 border border-transparent hover:border-blue-100 dark:hover:border-blue-800"
          >
            <font-awesome-icon icon="home" class="w-5 h-5 mr-3" />
            <span>Kembali ke Halaman Utama</span>
          </a>
        </div>
      </div>
    </aside>

    <!-- Main Content -->
    <div
      :class="[
        'min-h-screen transition-all duration-300',
        {
          'lg:ml-72': showingSidebar && !isMobile,
          'lg:ml-20': !showingSidebar && !isMobile,
        },
      ]"
    >
      <!-- Top Header -->
      <header
        class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-sm border-b border-slate-200 dark:border-slate-700 sticky top-0 z-30"
      >
        <div class="flex justify-between items-center px-4 lg:px-6 py-4">
          <div class="flex items-center">
            <button
              @click="toggleSidebar"
              class="p-2 text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-all duration-200 mr-3"
            >
              <font-awesome-icon icon="bars" class="h-5 w-5" />
            </button>
          </div>

          <div class="flex items-center space-x-2">
            <!-- Dark Mode Toggle -->
            <button
              @click="toggleDarkMode"
              class="p-2 text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-all duration-200"
            >
              <font-awesome-icon :icon="isDark ? 'sun' : 'moon'" class="h-4 w-4" />
            </button>

            <!-- Language Switcher Dropdown -->
            <div class="relative language-dropdown-container">
              <button
                @click="toggleLanguageDropdown"
                class="flex items-center space-x-2 px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
              >
                <font-awesome-icon
                  icon="globe"
                  class="w-4 h-4 text-slate-500 dark:text-slate-400"
                />
                <span
                  class="text-sm font-medium text-slate-700 dark:text-slate-200 hidden md:inline"
                >
                  {{ languages[locale].name }}
                </span>
                <font-awesome-icon
                  icon="chevron-down"
                  class="w-3 h-3 text-slate-500 dark:text-slate-400"
                />
              </button>

              <!-- Language Dropdown Menu -->
              <div
                v-if="showLanguageDropdown"
                class="absolute right-0 mt-2 w-48 bg-white dark:bg-slate-800 rounded-lg shadow-lg border border-slate-200 dark:border-slate-700 py-1 z-50"
              >
                <button
                  v-for="(lang, code) in languages"
                  :key="code"
                  @click="switchLanguage(code)"
                  :class="[
                    'flex items-center justify-between w-full px-4 py-2.5 text-sm transition-colors',
                    locale === code
                      ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400'
                      : 'text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700',
                  ]"
                >
                  <span>{{ lang.name }}</span>
                  <font-awesome-icon
                    v-if="locale === code"
                    icon="check"
                    class="w-4 h-4 text-blue-600 dark:text-blue-400"
                  />
                </button>
              </div>
            </div>

            <!-- User Dropdown -->
            <div class="relative user-dropdown-container">
              <button
                @click="toggleUserDropdown"
                class="flex items-center space-x-2 p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
              >
                <div
                  class="w-8 h-8 rounded-full overflow-hidden flex items-center justify-center"
                >
                  <img
                    :src="
                      user.profile?.photo
                        ? `/storage/${user.profile.photo}`
                        : '/default.jpg'
                    "
                    :alt="user.name"
                    class="w-full h-full object-cover"
                  />
                </div>
                <span
                  class="text-sm font-medium text-slate-700 dark:text-slate-200 hidden md:inline"
                  >{{ user.name }}</span
                >
                <font-awesome-icon
                  icon="chevron-down"
                  class="w-3 h-3 text-slate-500 dark:text-slate-400"
                />
              </button>

              <!-- Dropdown Menu -->
              <div
                v-if="showUserDropdown"
                class="absolute right-0 mt-2 w-48 bg-white dark:bg-slate-800 rounded-lg shadow-lg border border-slate-200 dark:border-slate-700 py-1 z-50"
              >
                <Link
                  href="/profile"
                  class="flex items-center space-x-2 px-4 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors"
                >
                  <font-awesome-icon
                    icon="user"
                    class="w-4 h-4 text-slate-500 dark:text-slate-400"
                  />
                  <span>My Profile</span>
                </Link>
                <hr class="my-1 border-slate-200 dark:border-slate-700" />
                <Link
                  href="/logout"
                  method="post"
                  class="flex items-center space-x-2 px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                >
                  <font-awesome-icon icon="sign-out-alt" class="w-4 h-4" />
                  <span>Logout</span>
                </Link>
              </div>
            </div>
          </div>
        </div>
      </header>

      <!-- Page Content -->
      <main class="p-4 lg:p-6">
        <slot />
      </main>
    </div>
  </div>
</template>
