@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 focus:border-brand-500 dark:focus:border-brand-400 focus:ring-brand-500 dark:focus:ring-brand-400 rounded-md shadow-sm']) }}>
