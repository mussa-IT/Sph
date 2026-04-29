import React, { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { WagmiProvider } from 'wagmi';
import { wagmiConfig } from './wagmi.js';
import WalletConnect from './WalletConnect.jsx';
import ProjectPublisher from './ProjectPublisher.jsx';

// Create a client
const queryClient = new QueryClient();

// Main Web3 App Component
const Web3App = () => {
  return (
    <StrictMode>
      <WagmiProvider config={wagmiConfig}>
        <QueryClientProvider client={queryClient}>
          <WalletConnect />
        </QueryClientProvider>
      </WagmiProvider>
    </StrictMode>
  );
};

// Initialize Web3 when DOM is ready
const initWeb3 = () => {
  const container = document.getElementById('wallet-connect-container');
  if (container) {
    const root = createRoot(container);
    root.render(<Web3App />);
  }
};

// Auto-initialize when DOM is ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initWeb3);
} else {
  initWeb3();
}

// Export for manual initialization if needed
export { initWeb3, Web3App, ProjectPublisher };

// Make available globally for Blade templates
if (typeof window !== 'undefined') {
  window.React = React;
  window.ReactDOM = { createRoot };
  window.ProjectPublisher = ProjectPublisher;
}
