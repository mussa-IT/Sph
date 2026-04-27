import React, { useState } from 'react';
import { useAccount, useWriteContract, useWaitForTransactionReceipt } from 'wagmi';
import { PROJECT_OWNERSHIP_CONTRACT } from './config.js';
import { getExplorerLink, formatAddress } from './wagmi.js';

const PublishProjectOnchain = ({ project, onSuccess, onError }) => {
  const { address, isConnected } = useAccount();
  const { writeContract, isPending: isWritePending } = useWriteContract();
  
  const [isPublishing, setIsPublishing] = useState(false);
  const [projectHash, setProjectHash] = useState('');
  const [error, setError] = useState('');
  const [success, setSuccess] = useState(false);
  const [txHash, setTxHash] = useState('');

  // Generate project hash from project data
  const generateProjectHash = (projectData) => {
    const dataString = JSON.stringify({
      id: projectData.id,
      title: projectData.title,
      description: projectData.description,
      user_id: projectData.user_id,
      created_at: projectData.created_at,
      updated_at: projectData.updated_at
    });
    
    // Simple hash generation (in production, use proper SHA256)
    let hash = 0;
    for (let i = 0; i < dataString.length; i++) {
      const char = dataString.charCodeAt(i);
      hash = ((hash << 5) - hash) + char;
      hash = hash & hash; // Convert to 32-bit integer
    }
    
    return '0x' + Math.abs(hash).toString(16).padStart(64, '0');
  };

  React.useEffect(() => {
    if (project) {
      const hash = generateProjectHash(project);
      setProjectHash(hash);
    }
  }, [project]);

  const handlePublish = async () => {
    if (!isConnected) {
      setError('Please connect your wallet first');
      return;
    }

    if (!address) {
      setError('Wallet address not available');
      return;
    }

    setIsPublishing(true);
    setError('');

    try {
      // First, register onchain
      await writeContract({
        address: PROJECT_OWNERSHIP_CONTRACT.address,
        abi: PROJECT_OWNERSHIP_CONTRACT.abi,
        functionName: 'registerProject',
        args: [projectHash, project.title || 'Untitled Project'],
      });

    } catch (contractError) {
      console.error('Contract error:', contractError);
      setError('Failed to register project onchain. Please try again.');
      setIsPublishing(false);
    }
  };

  const { data: receipt, isLoading: isConfirming } = useWaitForTransactionReceipt({
    hash: txHash,
    onSuccess: (data) => {
      // Update backend with transaction details
      updateBackendWithTransaction(data);
    },
    onError: (error) => {
      console.error('Transaction failed:', error);
      setError('Transaction failed. Please try again.');
      setIsPublishing(false);
    }
  });

  const updateBackendWithTransaction = async (receipt) => {
    try {
      const response = await fetch(`/web3/projects/${project.id}/publish`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify({
          transaction_hash: receipt.transactionHash,
          wallet_address: address
        })
      });

      const result = await response.json();

      if (result.success) {
        setSuccess(true);
        setTxHash(receipt.transactionHash);
        onSuccess?.(result.data);
      } else {
        setError(result.message || 'Failed to update project records');
        onError?.(result.message);
      }
    } catch (backendError) {
      console.error('Backend error:', backendError);
      setError('Project registered onchain but failed to update records');
      onError?.('Backend update failed');
    } finally {
      setIsPublishing(false);
    }
  };

  const reset = () => {
    setError('');
    setSuccess(false);
    setTxHash('');
  };

  if (!project) {
    return (
      <div className="p-4 text-center text-muted">
        No project selected for publishing
      </div>
    );
  }

  if (success && txHash) {
    return (
      <div className="p-6">
        <div className="text-center mb-6">
          <div className="text-4xl mb-3">✅</div>
          <h3 className="text-lg font-semibold text-foreground dark:text-foreground-dark mb-2">
            Project Published Onchain!
          </h3>
          <p className="text-sm text-muted dark:text-muted-dark">
            Your project is now permanently recorded on Base Sepolia
          </p>
        </div>

        <div className="space-y-3">
          <div className="p-3 rounded-lg bg-muted/10 dark:bg-muted-dark/20">
            <div className="flex items-center justify-between">
              <span className="text-sm font-medium">Transaction Hash:</span>
              <button
                onClick={() => navigator.clipboard.writeText(txHash)}
                className="text-xs text-primary hover:text-primary/80"
              >
                Copy
              </button>
            </div>
            <code className="block text-xs mt-1 break-all">{txHash}</code>
          </div>

          <div className="p-3 rounded-lg bg-muted/10 dark:bg-muted-dark/20">
            <div className="flex items-center justify-between">
              <span className="text-sm font-medium">Project Hash:</span>
              <button
                onClick={() => navigator.clipboard.writeText(projectHash)}
                className="text-xs text-primary hover:text-primary/80"
              >
                Copy
              </button>
            </div>
            <code className="block text-xs mt-1 break-all">{projectHash}</code>
          </div>

          <div className="p-3 rounded-lg bg-muted/10 dark:bg-muted-dark/20">
            <div className="flex items-center justify-between">
              <span className="text-sm font-medium">Wallet Address:</span>
              <button
                onClick={() => navigator.clipboard.writeText(address)}
                className="text-xs text-primary hover:text-primary/80"
              >
                Copy
              </button>
            </div>
            <code className="block text-xs mt-1">{formatAddress(address)}</code>
          </div>

          <div className="flex gap-2">
            <a
              href={getExplorerLink('tx', txHash)}
              target="_blank"
              rel="noopener noreferrer"
              className="btn-brand text-sm flex-1 text-center"
            >
              View on BaseScan
            </a>
            <button
              onClick={reset}
              className="btn-brand-muted text-sm flex-1"
            >
              Publish Another
            </button>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="p-6">
      <h3 className="text-lg font-semibold text-foreground dark:text-foreground-dark mb-4">
        Publish Project Onchain
      </h3>
      
      <div className="space-y-4">
        <div className="p-4 rounded-lg bg-muted/10 dark:bg-muted-dark/20">
          <h4 className="font-medium text-foreground dark:text-foreground-dark mb-2">
            {project.title}
          </h4>
          <p className="text-sm text-muted dark:text-muted-dark">
            {project.description || 'No description available'}
          </p>
        </div>

        <div className="p-4 rounded-lg bg-info/10 border border-info/20">
          <div className="flex items-start gap-3">
            <span className="text-xl">ℹ️</span>
            <div>
              <h4 className="font-medium text-foreground dark:text-foreground-dark mb-1">
                Onchain Publishing
              </h4>
              <ul className="text-sm text-muted dark:text-muted-dark space-y-1">
                <li>• Creates immutable proof of ownership</li>
                <li>• Records timestamp on Base Sepolia</li>
                <li>• Enables public verification</li>
                <li>• Costs gas fees (testnet ETH)</li>
              </ul>
            </div>
          </div>
        </div>

        {!isConnected && (
          <div className="p-4 rounded-lg bg-warning/10 border border-warning/20">
            <div className="flex items-start gap-3">
              <span className="text-xl">⚠️</span>
              <div>
                <h4 className="font-medium text-foreground dark:text-foreground-dark mb-1">
                  Wallet Required
                </h4>
                <p className="text-sm text-muted dark:text-muted-dark">
                  Connect your wallet to publish this project onchain
                </p>
              </div>
            </div>
          </div>
        )}

        {error && (
          <div className="p-4 rounded-lg bg-danger/10 border border-danger/20">
            <div className="flex items-start gap-3">
              <span className="text-xl">❌</span>
              <div>
                <h4 className="font-medium text-danger mb-1">Error</h4>
                <p className="text-sm text-danger">{error}</p>
              </div>
            </div>
          </div>
        )}

        <button
          onClick={handlePublish}
          disabled={!isConnected || isPublishing || isWritePending || isConfirming}
          className="btn-brand w-full"
        >
          {isWritePending || isConfirming ? (
            <>
              <span className="w-4 h-4 border-2 border-current border-t-transparent rounded-full animate-spin inline-block mr-2"></span>
              {isConfirming ? 'Confirming...' : 'Registering...'}
            </>
          ) : isPublishing ? (
            <>
              <span className="w-4 h-4 border-2 border-current border-t-transparent rounded-full animate-spin inline-block mr-2"></span>
              Publishing...
            </>
          ) : (
            <>
              <span className="mr-2">🔗</span>
              Publish Onchain
            </>
          )}
        </button>

        <div className="text-xs text-muted dark:text-muted-dark text-center">
          Network: Base Sepolia Testnet
        </div>
      </div>
    </div>
  );
};

export default PublishProjectOnchain;
