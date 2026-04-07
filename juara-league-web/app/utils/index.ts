export function randomInt(min: number, max: number): number {
  return Math.floor(Math.random() * (max - min + 1)) + min
}

export function randomFrom<T>(array: T[]): T {
  return array[Math.floor(Math.random() * array.length)] as T
}

export function formatCurrency(amount: number | string | undefined | null) {
  if (amount === undefined || amount === null) return 'Rp 0'
  const value = typeof amount === 'string' ? parseInt(amount) : amount
  if (isNaN(value)) return 'Rp 0'
  
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(value)
}
