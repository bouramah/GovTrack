import Image from 'next/image';

interface MinistryLogoProps {
  size?: 'sm' | 'md' | 'lg';
  showText?: boolean;
  className?: string;
}

export default function MinistryLogo({ size = 'md', showText = true, className = '' }: MinistryLogoProps) {
  const sizeClasses = {
    sm: 'w-12 h-12',
    md: 'w-16 h-16',
    lg: 'w-20 h-20'
  };

  const textSizeClasses = {
    sm: 'text-sm',
    md: 'text-base',
    lg: 'text-lg'
  };

  return (
    <div className={`flex items-center gap-2 ${className}`}>
      <div className={`relative ${sizeClasses[size]} flex-shrink-0`}>
        <Image
          src="/logo.jpg"
          alt="Logo du Ministère de la Sécurité et de la Protection Civile"
          width={100}
          height={100}
          className="w-full h-full object-contain rounded-full"
          priority
        />
      </div>
      {showText && (
        <div className="flex flex-col">
          <span className={`font-bold text-gray-900 ${textSizeClasses[size]}`}>
            Ministère de la Sécurité
          </span>
          <span className={`font-bold text-gray-900 ${textSizeClasses[size]}`}>
            et de la Protection Civile
          </span>
        </div>
      )}
    </div>
  );
}