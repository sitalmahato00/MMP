import { useQuery } from '@tanstack/react-query';
import { getLeadership } from '@shared/services/public.service';

export default function LeadershipPage() {
  const { data, isLoading } = useQuery({ queryKey: ['public-leadership'], queryFn: getLeadership });
  const presidents: any[] = data?.presidents ?? [];
  const principals: any[] = data?.principals ?? [];

  if (isLoading) {
    return (
      <div className="flex justify-center py-32">
        <div className="w-10 h-10 border-4 border-[#003D82] border-t-transparent rounded-full animate-spin" />
      </div>
    );
  }

  return (
    <div className="bg-white border-t border-gray-100">
      <div className="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-12">

        {/* Presidents */}
        <div className="mb-16">
          <div className="bg-[#003D82] border-b-2 border-yellow-500 text-white text-center py-3 rounded-t-lg shadow font-bold text-lg mb-8">
            Presidents of MMP
          </div>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-16 justify-center">
            {presidents.length > 0 ? presidents.map((p: any, i: number) => (
              <div key={i} className="flex flex-col items-center text-center">
                <div className="w-48 h-64 md:w-56 md:h-72 rounded-full overflow-hidden border-[6px] border-white shadow-lg mb-6 bg-gray-100">
                  {p.avatar ? (
                    <img src={`/storage/${p.avatar}`} alt={p.name} className="w-full h-full object-cover" />
                  ) : (
                    <div className="w-full h-full flex items-center justify-center text-4xl font-bold"
                      style={{ background: 'linear-gradient(135deg, #f1f5f9, #e2e8f0)', color: '#64748b' }}>
                      {(p.name ?? 'P')[0].toUpperCase()}
                    </div>
                  )}
                </div>
                <h3 className="font-bold text-gray-800 text-lg mb-1">{p.name}</h3>
                <div className="text-sm text-gray-600 mb-1">{p.designation || 'President'}</div>
                <div className="text-sm text-gray-500 font-medium">
                  ({p.start_date_bs} to {p.end_date_bs || 'till now'})BS
                </div>
                {p.message && (
                  <div className="mt-4 text-sm text-gray-600 italic">
                    &ldquo;{p.message.length > 150 ? `${p.message.slice(0, 150)}…` : p.message}&rdquo;
                  </div>
                )}
              </div>
            )) : (
              <div className="col-span-full text-center text-gray-500 py-8">No records available.</div>
            )}
          </div>
        </div>

        {/* Principals */}
        <div>
          <div className="bg-[#003D82] border-b-2 border-yellow-500 text-white text-center py-3 rounded-t-lg shadow font-bold text-lg mb-8">
            Principals of MMP
          </div>
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8 justify-center">
            {principals.length > 0 ? principals.map((p: any, i: number) => (
              <div key={i} className="flex flex-col items-center text-center">
                <div className="w-40 h-56 md:w-48 md:h-64 rounded-[40%] overflow-hidden border-4 border-white shadow-md mb-5 bg-gray-100">
                  {p.avatar ? (
                    <img src={`/storage/${p.avatar}`} alt={p.name} className="w-full h-full object-cover" />
                  ) : (
                    <div className="w-full h-full flex items-center justify-center text-3xl font-bold"
                      style={{ background: 'linear-gradient(135deg, #f1f5f9, #e2e8f0)', color: '#64748b' }}>
                      {(p.name ?? 'P')[0].toUpperCase()}
                    </div>
                  )}
                </div>
                <h3 className="font-bold text-gray-800 text-md mb-1">{p.name}</h3>
                <div className="text-xs text-gray-600 mb-1">{p.designation || 'Principal'}</div>
                <div className="text-xs text-gray-500 font-medium">
                  ({p.start_date_bs} to {p.end_date_bs || 'till now'})BS
                </div>
              </div>
            )) : (
              <div className="col-span-full text-center text-gray-500 py-8">No records available.</div>
            )}
          </div>
        </div>

      </div>
    </div>
  );
}
