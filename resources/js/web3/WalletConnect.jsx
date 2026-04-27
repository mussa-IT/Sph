import React, { useState, useEffect } from 'react';
import { useAccount, useConnect, useDisconnect, useChainId, useSwitchChain } from 'wagmi';
import { SUPPORTED_WALLETS, BASE_SEPOLIA, switchToBaseSepolia, formatAddress } from './config.js';
import { getNetworkStatus } from './wagmi.js';

const WalletConnect = () => {
  const [isOpen, setIsOpen] = useState(false);
  const [isConnecting, setIsConnecting] = useState(false);
  const { address, isConnected } = useAccount();
  const { connectors, connect, isPending } = useConnect();
  const { disconnect } = useDisconnect();
  const chainId = useChainId();
  const { switchChain } = useSwitchChain();

  const isWrongNetwork = chainId !== BASE_SEPOLIA.chainId;

  useEffect(() => {
    const handleClickOutside = (event) => {
      if (!event.target.closest('.wallet-dropdown')) {
        setIsOpen(false);
      }
    };

    document.addEventListener('click', handleClickOutside);
    return () => document.removeEventListener('click', handleClickOutside);
  }, []);

  const handleConnect = async (connector) => {
    setIsConnecting(true);
    try {
      await connect({ connector });
      setIsOpen(false);
    } catch (error) {
      console.error('Connection failed:', error);
    } finally {
      setIsConnecting(false);
    }
  };

  const handleDisconnect = () => {
    disconnect();
    setIsOpen(false);
  };

  const handleSwitchNetwork = async () => {
    const success = await switchToBaseSepolia();
    if (success) {
      window.location.reload();
    }
  };

  if (isConnected) {
    return (
      <div className="wallet-dropdown relative">
        <button
          onClick={() => setIsOpen(!isOpen)}
          className="flex items-center gap-2 px-4 py-2 rounded-xl border border-muted/20 bg-background hover:bg-muted/10 transition-colors"
        >
          <span className="w-2 h-2 rounded-full bg-green-500"></span>
          <span className="text-sm font-medium">{formatAddress(address)}</span>
          {isWrongNetwork && (
            <span className="text-xs text-warning">Wrong Network</span>
          )}
          <span className="text-muted">▾</span>
        </button>

        {isOpen && (
          <div className="absolute right-0 top-full mt-2 w-64 rounded-2xl border border-muted/20 bg-background/95 backdrop-blur-xl shadow-2xl z-50">
            <div className="p-4 border-b border-muted/10">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm font-medium">Connected</p>
                  <p className="text-xs text-muted">{formatAddress(address)}</p>
                </div>
                {isWrongNetwork ? (
                  <button
                    onClick={handleSwitchNetwork}
                    className="text-xs px-2 py-1 rounded-lg bg-warning/10 text-warning hover:bg-warning/20"
                  >
                    Switch to Base
                  </button>
                ) : (
                  <span className="text-xs px-2 py-1 rounded-lg bg-success/10 text-success">
                    Base Sepolia
                  </span>
                )}
              </div>
            </div>

            <div className="p-2">
              <button
                onClick={() => window.open(`https://sepolia.basescan.org/address/${address}`, '_blank')}
                className="w-full text-left px-3 py-2 text-sm rounded-lg hover:bg-muted/10 transition-colors"
              >
                View on BaseScan
              </button>
              <button
                onClick={() => navigator.clipboard.writeText(address)}
                className="w-full text-left px-3 py-2 text-sm rounded-lg hover:bg-muted/10 transition-colors"
              >
                Copy Address
              </button>
              <hr className="my-2 border-muted/10" />
              <button
                onClick={handleDisconnect}
                className="w-full text-left px-3 py-2 text-sm rounded-lg hover:bg-danger/10 text-danger transition-colors"
              >
                Disconnect
              </button>
            </div>
          </div>
        )}
      </div>
    );
  }

  return (
    <div className="wallet-dropdown relative">
      <button
        onClick={() => setIsOpen(!isOpen)}
        className="btn-brand flex items-center gap-2"
        disabled={isPending || isConnecting}
      >
        <span className="w-4 h-4 border-2 border-current border-t-transparent rounded-full animate-spin"></span>
        Connect Wallet
      </button>

      {isOpen && (
        <div className="absolute right-0 top-full mt-2 w-72 rounded-2xl border border-muted/20 bg-background/95 backdrop-blur-xl shadow-2xl z-50">
          <div className="p-4">
            <h3 className="text-sm font-semibold mb-3">Connect Wallet</h3>
            <p className="text-xs text-muted mb-4">
              Connect your wallet to access onchain features on Base Sepolia testnet
            </p>
          </div>

          <div className="p-2 space-y-1">
            {connectors.map((connector) => {
              const wallet = Object.values(SUPPORTED_WALLETS).find(
                (w) => w.name.toLowerCase().includes(connector.name.toLowerCase()) ||
                         connector.name.toLowerCase().includes(w.name.toLowerCase())
              ) || { name: connector.name, icon: '🔗' };

              return (
                <button
                  key={connector.uid}
                  onClick={() => handleConnect(connector)}
                  disabled={!connector.ready || isPending || isConnecting}
                  className="w-full flex items-center gap-3 px-3 py-3 rounded-xl hover:bg-muted/10 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <span className="text-xl">{wallet.icon}</span>
                  <div className="text-left">
                    <p className="text-sm font-medium">{wallet.name}</p>
                    {!connector.ready && (
                      <p className="text-xs text-warning">Not installed</p>
                    )}
                  </div>
                  {(isPending || isConnecting) && (
                    <span className="ml-auto w-4 h-4 border-2 border-current border-t-transparent rounded-full animate-spin"></span>
                  )}
                </button>
              );
            })}
          </div>

          <div className="p-4 border-t border-muted/10">
            <div className="flex items-center gap-2 text-xs text-muted">
              <span className="w-2 h-2 rounded-full bg-info"></span>
              Base Sepolia Testnet
            </div>
            <p className="text-xs text-muted mt-1">
              Get test ETH from{' '}
              <a 
                href="https://sepoliafaucet.com/" 
                target="_blank" 
                rel="noopener noreferrer"
                className="text-primary hover:underline"
              >
                Base Sepolia Faucet
              </a>
            </p>
          </div>
        </div>
      )}
    </div>
  );
};

export default WalletConnect;
