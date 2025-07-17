import Image from 'next/image';

interface MinistryLogoProps {
  size?: 'sm' | 'md' | 'lg';
  showText?: boolean;
  className?: string;
}

export default function MinistryLogo({ size = 'md', showText = true, className = '' }: MinistryLogoProps) {
  const sizeClasses = {
    sm: 'w-8 h-8',
    md: 'w-10 h-10',
    lg: 'w-12 h-12'
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
          width={48}
          height={48}
          className="w-full h-full object-contain rounded-full"
          priority
        />
      </div>
      {showText && (
        <div className="flex flex-col">
          <span className={`font-semibold text-gray-900 ${textSizeClasses[size]}`}>
            Ministère de la Sécurité
          </span>
          <span className={`text-gray-600 ${textSizeClasses[size]}`}>
            et de la Protection Civile
          </span>
        </div>
      )}
    </div>
  );
}