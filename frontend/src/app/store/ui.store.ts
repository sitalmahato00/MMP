import { createSlice, type PayloadAction } from '@reduxjs/toolkit';

interface UiState {
  sidebarOpen: boolean;
  theme: 'light' | 'dark';
}

function loadSidebarState(): boolean {
  try {
    const saved = localStorage.getItem('sidebar_open');
    return saved !== null ? JSON.parse(saved) : true;
  } catch {
    return true;
  }
}

const initialState: UiState = {
  sidebarOpen: loadSidebarState(),
  theme: 'light',
};

const uiSlice = createSlice({
  name: 'ui',
  initialState,
  reducers: {
    toggleSidebar(state) {
      state.sidebarOpen = !state.sidebarOpen;
      localStorage.setItem('sidebar_open', JSON.stringify(state.sidebarOpen));
    },
    setSidebarOpen(state, action: PayloadAction<boolean>) {
      state.sidebarOpen = action.payload;
      localStorage.setItem('sidebar_open', JSON.stringify(state.sidebarOpen));
    },
  },
});

export const { toggleSidebar, setSidebarOpen } = uiSlice.actions;
export default uiSlice.reducer;
