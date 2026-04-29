import React, { useState } from 'react';
import { useAccount, useWriteContract, useWaitForTransactionReceipt } from 'wagmi';
import { PROJECT_OWNERSHIP_CONTRACT, BASE_SEPOLIA, getExplorerLink } from './config.js';
import { keccak256, toBytes } from 'viem';

const ProjectPublisher = ({ project, onSuccess }) => {
  const [isPublishing, setIsPublishing] = useState(false);
  const [error, setError] = useState(null);
  const { address, isConnected } = useAccount();
  
  const { data: hash, writeContract, isPending } = useWriteContract();
  
  const { isLoading: isConfirming, isSuccess: isConfirmed } = useWaitForTransactionReceipt({
    hash,
  });

  // Generate project hash
  const generateProjectHash = () => {
    const projectData = JSON.stringify({
      id: project.id,
      title: project.title,
      description: project.description,
      user_id: project.user_id,
      created_at: project.created_at,
    });
    
    return keccak256(toBytes(projectData));
  };

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
    setError(null);

    try {
      const projectHash = generateProjectHash();
      
      // Call smart contract
      writeContract({
        address: PROJECT_OWNERSHIP_CONTRACT.address,
        abi: PROJECT_OWNERSHIP_CONTRACT.abi,
        functionName: 'registerProject',
        args: [projectHash, project.title],
      });
    } catch (err) {
      console.error('Publish error:', err);
      setError(err.message || 'Failed to publish project onchain');
      setIsPublishing(false);
    }
  };

  // Handle successful transaction
  React.useEffect(() => {
    if (isConfirmed && hash) {
      // Save transaction data to backend
      saveTransactionToBackend(hash);
    }
  }, [isConfirmed, hash]);

  const saveTransactionToBackend = async (txHash) => {
    try {
      const response = await fetch(`/web3/projects/${project.id}/publish`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify({
          transaction_hash: txHash,
          wallet_address: address,
        }),
      });

      const result = await response.json();
      
      if (result.success) {
        setIsPublishing(false);
        if (onSuccess) {
          onSuccess(result.data);
        }
      } else {
        setError(result.message || 'Failed to save transaction');
        setIsPublishing(false);
      }
    } catch (err) {
      console.error('Save transaction error:', err);
      setError('Failed to save transaction to database');
      setIsPublishing(false);
    }
  };

  // Check if already published
  const isPublished = project.transaction_hash && project.blockchain_verified_at;

  if (isPublished) {
    return (
      <div className="p-4 rounded-xl border border-success/20 bg-success/5">
        <div className="flex items-center gap-3">
          <span className="text-2xl">✅</span>
          <div className="flex-1">
            <p className="font-medium text-foreground dark:text-foreground-dark">Published Onchain</p>
            <p className="text-sm text-muted dark:text-muted-dark">
              Verified on {new Date(project.blockchain_verified_at).toLocaleDateString()}
            </p>
          </div>
          <a
            href={getExplorerLink('tx', project.transaction_hash)}
            target="_blank"
            rel="noopener noreferrer"
            className="text-sm text-primary hover:text-primary/80"
          >
            View Transaction
          </a>
        </div>
      </div>
    );
  }

  return (
    <div className="p-4 rounded-xl border border-muted/20 bg-muted/5">
      <div className="flex items-start justify-between mb-4">
        <div>
          <h3 className="font-semibold text-foreground dark:text-foreground-dark">Publish Ownership Onchain</h3>
          <p className="text-sm text-muted dark:text-muted-dark mt-1">
            Register your project on Base Sepolia blockchain for immutable ownership proof
          </p>
        </div>
        <span className="text-2xl">🔗</span>
      </div>

      {error && (
        <div className="mb-4 p-3 rounded-lg bg-danger/10 text-danger text-sm">
          {error}
        </div>
      )}

      <div className="space-y-3">
        <div className="flex items-center gap-2 text-sm text-muted dark:text-muted-dark">
          <span className="w-2 h-2 rounded-full bg-info"></span>
          <span>Network: Base Sepolia Testnet</span>
        </div>
        
        <div className="flex items-center gap-2 text-sm text-muted dark:text-muted-dark">
          <span className="w-2 h-2 rounded-full bg-info"></span>
          <span>Gas fees: Minimal (testnet)</span>
        </div>

        {!isConnected ? (
          <p className="text-sm text-warning">
            Connect your wallet to publish this project
          </p>
        ) : (
          <button
            onClick={handlePublish}
            disabled={isPublishing || isPending || isConfirming}
            className="btn-brand w-full"
          >
            {(isPublishing || isPending || isConfirming) ? (
              <span className="flex items-center justify-center gap-2">
                <span className="w-4 h-4 border-2 border-current border-t-transparent rounded-full animate-spin"></span>
                {isConfirming ? 'Confirming...' : 'Publishing...'}
              </span>
            ) : (
              'Publish Onchain'
            )}
          </button>
        )}
      </div>

      {hash && (
        <div className="mt-4 p-3 rounded-lg bg-info/10 text-sm">
          <p className="font-medium text-foreground dark:text-foreground-dark mb-1">Transaction Submitted</p>
          <p className="text-muted dark:text-muted-dark break-all">{hash}</p>
          <a
            href={getExplorerLink('tx', hash)}
            target="_blank"
            rel="noopener noreferrer"
            className="text-primary hover:text-primary/80 mt-2 inline-block"
          >
            View on BaseScan
          </a>
        </div>
      )}
    </div>
  );
};

export default ProjectPublisher;
