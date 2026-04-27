// Web3 Configuration for Smart Project Hub
export const BASE_SEPOLIA = {
  chainId: 84532,
  name: 'Base Sepolia',
  network: 'base-sepolia',
  nativeCurrency: {
    name: 'ETH',
    symbol: 'ETH',
    decimals: 18,
  },
  rpcUrls: {
    public: { http: ['https://sepolia.base.org'] },
    default: { http: ['https://sepolia.base.org'] },
  },
  blockExplorers: {
    default: { name: 'BaseScan', url: 'https://sepolia.basescan.org' },
    base: { name: 'BaseScan', url: 'https://sepolia.basescan.org' },
  },
  testnet: true,
};

export const PROJECT_OWNERSHIP_CONTRACT = {
  address: process.env.VITE_PROJECT_OWNERSHIP_CONTRACT || '0x0000000000000000000000000000000000000000',
  abi: [
    {
      "inputs": [
        {"internalType": "string", "name": "projectHash", "type": "string"},
        {"internalType": "string", "name": "title", "type": "string"}
      ],
      "name": "registerProject",
      "outputs": [],
      "stateMutability": "nonpayable",
      "type": "function"
    },
    {
      "anonymous": false,
      "inputs": [
        {"indexed": true, "internalType": "address", "name": "owner", "type": "address"},
        {"indexed": true, "internalType": "string", "name": "projectHash", "type": "string"},
        {"indexed": false, "internalType": "string", "name": "title", "type": "string"},
        {"indexed": false, "internalType": "uint256", "name": "timestamp", "type": "uint256"}
      ],
      "name": "ProjectRegistered",
      "type": "event"
    },
    {
      "inputs": [{"internalType": "string", "name": "projectHash", "type": "string"}],
      "name": "getProject",
      "outputs": [
        {"internalType": "address", "name": "owner", "type": "address"},
        {"internalType": "string", "name": "title", "type": "string"},
        {"internalType": "uint256", "name": "timestamp", "type": "uint256"}
      ],
      "stateMutability": "view",
      "type": "function"
    }
  ]
};

export const BADGE_CONTRACT = {
  address: process.env.VITE_BADGE_CONTRACT || '0x0000000000000000000000000000000000000000',
  abi: [
    {
      "inputs": [
        {"internalType": "address", "name": "to", "type": "address"},
        {"internalType": "uint256", "name": "tokenId", "type": "uint256"},
        {"internalType": "string", "name": "uri", "type": "string"}
      ],
      "name": "mintBadge",
      "outputs": [],
      "stateMutability": "nonpayable",
      "type": "function"
    },
    {
      "inputs": [{"internalType": "uint256", "name": "tokenId", "type": "uint256"}],
      "name": "tokenURI",
      "outputs": [{"internalType": "string", "name": "", "type": "string"}],
      "stateMutability": "view",
      "type": "function"
    },
    {
      "inputs": [
        {"internalType": "address", "name": "from", "type": "address"},
        {"internalType": "address", "name": "to", "type": "address"},
        {"internalType": "uint256", "name": "tokenId", "type": "uint256"}
      ],
      "name": "transferFrom",
      "outputs": [],
      "stateMutability": "nonpayable",
      "type": "function"
    }
  ]
};

export const BADGE_TYPES = {
  PROJECT_COMPLETED: 1,
  TOP_INNOVATOR: 2,
  MENTOR: 3,
  TASKS_100: 4,
  EARLY_ADOPTER: 5,
  VERIFIED_BUILDER: 6
};

export const WALLET_CONNECT_PROJECT_ID = process.env.VITE_WALLET_CONNECT_PROJECT_ID || 'your-project-id';

export const SUPPORTED_WALLETS = {
  injected: {
    name: 'MetaMask',
    icon: '🦊'
  },
  walletConnect: {
    name: 'WalletConnect',
    icon: '🔗'
  },
  coinbaseWallet: {
    name: 'Coinbase Wallet',
    icon: '🔵'
  }
};

export const switchToBaseSepolia = async () => {
  if (typeof window.ethereum !== 'undefined') {
    try {
      await window.ethereum.request({
        method: 'wallet_switchEthereumChain',
        params: [{ chainId: '0x14a34' }], // Base Sepolia chainId in hex
      });
    } catch (switchError) {
      // This error code indicates that the chain has not been added to MetaMask
      if (switchError.code === 4902) {
        try {
          await window.ethereum.request({
            method: 'wallet_addEthereumChain',
            params: [
              {
                chainId: '0x14a34',
                chainName: 'Base Sepolia',
                nativeCurrency: {
                  name: 'ETH',
                  symbol: 'ETH',
                  decimals: 18,
                },
                rpcUrls: ['https://sepolia.base.org'],
                blockExplorerUrls: ['https://sepolia.basescan.org'],
              },
            ],
          });
        } catch (addError) {
          console.error('Failed to add Base Sepolia network:', addError);
        }
      } else {
        console.error('Failed to switch to Base Sepolia:', switchError);
      }
    }
  }
};

export const formatAddress = (address) => {
  if (!address) return '';
  return `${address.slice(0, 6)}...${address.slice(-4)}`;
};
