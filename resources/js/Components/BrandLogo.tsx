import { cn } from '@/lib/utils';

export const logoConfig = {
  src: '/assets/asshodiqiyah/logo.webp',
  alt: 'Logo Pondok Pesantren Asshodiqiyah Kaligawe',
};

export function BrandLogo({
  className,
}: {
  className?: string;
}) {
  return (
    <span
      className={cn(
        'inline-flex shrink-0 items-center justify-center border-0 bg-transparent p-0',
        className,
      )}
    >
      <img
        src={logoConfig.src}
        alt={logoConfig.alt}
        className="block max-h-full max-w-full object-contain"
      />
    </span>
  );
}
