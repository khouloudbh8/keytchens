// ==================== DATA ====================
let faqData = [];

// ==================== SVG ICONS ====================
const icons = {
  chevronDown: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>',
  search: '<svg class="w-10 h-10 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>',
};

// ==================== STATE ====================
let searchQuery = "";
let openItems = new Set();

// ==================== HELPERS ====================
function escapeRegExp(str) {
  return str.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

function highlightMatch(text, query) {
  if (!query) return text;
  const regex = new RegExp(`(${escapeRegExp(query)})`, 'gi');
  return text.replace(regex, '<span class="bg-yellow-200 text-slate-900 rounded px-0.5">$1</span>');
}

function updateResultCount(count) {
  const resultCount = document.getElementById('resultCount');
  if (!resultCount) return;
  if (!searchQuery.trim()) {
    resultCount.textContent = '';
  } else {
    resultCount.textContent = `${count} result${count > 1 ? 's' : ''} found`;
  }
}

// ==================== LOAD DATA ====================
async function loadFAQData() {
  const container = document.getElementById('faqContainer');
  if (container) {
    container.innerHTML = `
      <div class="text-center py-20">
        <p class="text-slate-500">Loading questions...</p>
      </div>
    `;
  }

  try {
    const response = await fetch('/src/features/faq/faq-data-en.json');
    if (!response.ok) throw new Error('Network error');
    faqData = await response.json();
  } catch (error) {
    console.error('Unable to load FAQ data:', error);
    if (container) {
      container.innerHTML = `
        <div class="text-center py-20">
          <p class="text-slate-500">Something went wrong while loading the questions. Please try again later.</p>
        </div>
      `;
    }
    return;
  }

  renderFAQ();
}

// ==================== RENDER FAQ ====================
function renderFAQ() {
  const container = document.getElementById('faqContainer');
  const query = searchQuery.trim().toLowerCase();

  let items = faqData;
  if (query) {
    items = faqData.filter(faq =>
      faq.question.toLowerCase().includes(query) ||
      faq.answer.toLowerCase().includes(query)
    );
  }

  updateResultCount(items.length);

  if (items.length === 0) {
    container.innerHTML = `
      <div class="text-center py-20 animate-fade-in">
        <div class="w-20 h-20 mx-auto mb-6 rounded-2xl bg-slate-100 flex items-center justify-center">
          ${icons.search}
        </div>
        <h3 class="text-xl font-semibold text-slate-900 mb-2">No results found</h3>
        <p class="text-slate-600 mb-6">Try a different keyword or contact us directly.</p>
        <button onclick="resetSearch()" class="inline-flex items-center px-6 py-2.5 rounded-full border border-slate-300 text-slate-700 font-medium hover:bg-slate-50 transition-all duration-200">
          Reset search
        </button>
      </div>
    `;
    return;
  }

  container.innerHTML = `
    <div class="space-y-4">
      ${items.map((faq, index) => {
        const isOpen = openItems.has(faq.id);
        const questionHtml = highlightMatch(faq.question, query);
        const answerHtml = highlightMatch(faq.answer, query);
        return `
          <div
            class="group bg-white rounded-2xl border transition-all duration-300 hover:shadow-xl hover:border-emerald-200 hover:-translate-y-0.5 ${
              isOpen
                ? 'border-emerald-300 shadow-lg shadow-emerald-500/5 bg-gradient-to-br from-white to-emerald-50/30'
                : 'border-slate-200 shadow-md'
            } animate-fade-in-up"
            style="animation-delay: ${index * 40}ms; animation-fill-mode: both;"
          >
            <button
              onclick="toggleItem('${faq.id}')"
              class="w-full px-6 py-5 flex items-center justify-between gap-4 text-left"
            >
              <h3 class="font-semibold text-base sm:text-lg transition-colors ${
                isOpen ? 'text-emerald-700' : 'text-slate-900 group-hover:text-emerald-600'
              }">
                ${questionHtml}
              </h3>
              <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center transition-all duration-300 ${
                isOpen
                  ? 'bg-emerald-100 text-emerald-600 rotate-180'
                  : 'bg-slate-100 text-slate-600 group-hover:bg-emerald-50 group-hover:text-emerald-600'
              }">
                ${icons.chevronDown}
              </div>
            </button>

            <div class="accordion-content ${isOpen ? 'open' : ''}">
              <div>
                <div class="px-6 pb-5 pt-1">
                  <div class="text-slate-600 leading-relaxed ${isOpen ? 'animate-slide-in-from-top' : ''}">
                    ${answerHtml}
                  </div>
                </div>
              </div>
            </div>
          </div>
        `;
      }).join('')}
    </div>
  `;
}

// ==================== ACTIONS ====================
function toggleItem(id) {
  if (openItems.has(id)) {
    openItems.delete(id);
  } else {
    openItems.add(id);
  }
  renderFAQ();
}

function resetSearch() {
  searchQuery = "";
  const input = document.getElementById('searchInput');
  if (input) input.value = "";
  renderFAQ();
}

// ==================== EVENT LISTENERS ====================
const searchInputEl = document.getElementById('searchInput');
if (searchInputEl) {
  searchInputEl.addEventListener('input', (e) => {
    searchQuery = e.target.value;
    renderFAQ();
  });

  searchInputEl.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      resetSearch();
      searchInputEl.blur();
    }
  });
}

// ==================== INIT ====================
loadFAQData();