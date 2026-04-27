import { createConfig, http, webSocket } from 'wagmi';
import { injected, walletConnect, coinbaseWallet } from 'wagmi/connectors';
import { BASE_SEPOLIA, WALLET_CONNECT_PROJECT_ID } from './config.js';

export const wagmiConfig = createConfig({
  chains: [BASE_SEPOLIA],
  connectors: [
    injected({
      shimDisconnect: true,
    }),
    walletConnect({
      projectId: WALLET_CONNECT_PROJECT_ID,
      showQrModal: false,
      metadata: {
        name: 'Smart Project Hub',
        description: 'AI-powered project management with onchain verification',
        url: typeof window !== 'undefined' ? window.location.origin : 'https://sph.example.com',
        icons: ['https://sph.example.com/icon.png'],
      },
    }),
    coinbaseWallet({
      appName: 'Smart Project Hub',
      appLogoUrl: 'https://sph.example.com/icon.png',
    }),
  ],
  transports: {
    [BASE_SEPOLIA.id]: http(),
  },
});

// Helper functions
export const getNetworkStatus = () => {
  if (typeof window === 'undefined') return null;
  
  const status = wagmiConfig.getState();
  return {
    isConnected: status.status === 'connected',
    chainId: status.chainId,
    address: status.account?.address,
    chain: status.chains?.[0],
  };
};

export const switchToBaseSepolia = async () => {
  if (typeof window === 'undefined') return false;
  
  try {
    await window.ethereum?.request({
      method: 'wallet_switchEthereumChain',
      params: [{ chainId: `0x${BASE_SEPOLIA.chainId.toString(16)}` }],
    });
    return true;
  } catch (error) {
    if (error.code === 4902) {
      // Chain not added, try to add it
      try {
        await window.ethereum?.request({
          method: 'wallet_addEthereumChain',
          params: [
            {
              chainId: `0x${BASE_SEPOLIA.chainId.toString(16)}`,
              chainName: BASE_SEPOLIA.name,
              rpcUrls: BASE_SEPOLIA.rpcUrls.default.http,
              nativeCurrency: BASE_SEPOLIA.nativeCurrency,
              blockExplorerUrls: [BASE_SEPOLIA.blockExplorers.default.url],
            },
          ],
        });
        return true;
      } catch (addError) {
        console.error('Failed to add Base Sepolia:', addError);
        return false;
      }
    }
    console.error('Failed to switch to Base Sepolia:', error);
    return false;
  }
};

export const formatAddress = (address, length = 6) => {
  if (!address) return '';
  return `${address.slice(0, length)}...${address.slice(-4)}`;
};

export const formatBalance = (balance, decimals = 18) => {
  if (!balance) return '0';
  const value = parseFloat(balance) / Math.pow(10, decimals);
  return value.toLocaleString(undefined, { maximumFractionDigits: 4 });
};

export const getExplorerLink = (type, value) => {
  const baseUrl = BASE_SEPOLIA.blockExplorers.default.url;
  switch (type) {
    case 'tx':
      return `${baseUrl}/tx/${value}`;
    case 'address':
      return `${baseUrl}/address/${value}`;
    case 'block':
      return `${baseUrl}/block/${value}`;
    default:
      return baseUrl;
  }
};
