import { BrowserRouter } from 'react-router-dom';
import { AppRouter } from '@app/router/AppRouter';
import { AuthInitializer } from '@app/providers/AuthProvider';

export default function App() {
  return (
    <BrowserRouter>
      <AuthInitializer>
        <AppRouter />
      </AuthInitializer>
    </BrowserRouter>
  );
}
