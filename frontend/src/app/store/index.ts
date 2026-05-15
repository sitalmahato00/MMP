import { configureStore } from '@reduxjs/toolkit';
import authReducer from './auth.store';
import uiReducer from './ui.store';

export const store = configureStore({
  reducer: {
    auth: authReducer,
    ui:   uiReducer,
  },
  middleware: (getDefaultMiddleware) =>
    getDefaultMiddleware({
      serializableCheck: {
        ignoredActions: ['auth/setUser'],
      },
    }),
});

export type RootState   = ReturnType<typeof store.getState>;
export type AppDispatch = typeof store.dispatch;
