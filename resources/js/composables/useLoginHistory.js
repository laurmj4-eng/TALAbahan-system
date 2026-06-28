import { ref } from 'vue';

const STORAGE_KEY = 'talabahan_login_history';
const MAX_ENTRIES = 5;

export function useLoginHistory() {
  const history = ref([]);

  function loadHistory() {
    try {
      const stored = localStorage.getItem(STORAGE_KEY);
      history.value = stored ? JSON.parse(stored) : [];
    } catch {
      history.value = [];
    }
    return history.value;
  }

  function saveToHistory(username) {
    if (!username || typeof username !== 'string') return;
    const entry = username.trim();
    if (!entry) return;

    let list = loadHistory();
    list = list.filter(e => e.toLowerCase() !== entry.toLowerCase());
    list.unshift(entry);
    if (list.length > MAX_ENTRIES) list = list.slice(0, MAX_ENTRIES);

    localStorage.setItem(STORAGE_KEY, JSON.stringify(list));
    history.value = list;
  }

  function removeFromHistory(username) {
    if (!username) return;
    let list = loadHistory();
    list = list.filter(e => e.toLowerCase() !== username.toLowerCase());
    localStorage.setItem(STORAGE_KEY, JSON.stringify(list));
    history.value = list;
  }

  loadHistory();

  return { history, loadHistory, saveToHistory, removeFromHistory };
}
